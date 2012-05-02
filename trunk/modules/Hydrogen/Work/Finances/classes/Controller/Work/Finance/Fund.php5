<?php
class Controller_Work_Finance_Fund extends CMF_Hydrogen_Controller{

	/**	@var	CMF_Hydrogen_Environment_Resource_Messenger		$messenger		Shortcut to messenger object */
	protected $messenger;

	protected function __onInit(){
		$this->messenger	= $this->env->getMessenger();
	}
	
	public function add(){
		$request	= $this->env->getRequest();
		$words		= $this->getWords( 'add' );
		$userId		= $this->env->getSession()->get( 'userId' );
		$model		= new Model_Finance_Fund( $this->env );

		if( $request->has( 'add' ) ){
			if( !strlen( trim( $request->get( 'title' ) ) ) )
				$this->messenger->noteError( $words->msgNoTitle );
			if( !strlen( trim( $request->get( 'ISIN' ) ) ) )
				$this->messenger->noteError( $words->msgNoISIN);
			if( !strlen( trim( $request->get( 'pieces' ) ) ) )
				$this->messenger->noteError( $words->msgNoPieces);
			if( !$this->messenger->gotError() ){
				$data	= array(
					'ISIN'			=> trim( $request->get( 'ISIN' ) ),
					'kag'			=> trim( $request->get( 'kag' ) ),
					'title'			=> trim( $request->get( 'title' ) ),
					'pieces'		=> trim( $request->get( 'pieces' ) ),
					'userId'		=> $userId,
					'createdAt'		=> time(),
				);
				$fundId	= $model->add( $data );
				$fund	= $model->get( $fundId );
				$this->messenger->noteSuccess( $words->msgSuccess, $fund->ISIN );
				$this->fetchFromYahoo( $fund );														//  fetch current fund price
				$this->restart( NULL, TRUE );
			}
		}
		$fund	= (object) array();
		foreach( $model->getColumns() as $column )
			$fund->$column	= $request->has( $column ) ? $request->get( $column ) : '';
		$this->addData( 'fund', $fund );
	}
	
	public function edit( $fundId ){
		$request	= $this->env->getRequest();
		$words		= $this->getWords( 'edit' );
		$userId		= $this->env->getSession()->get( 'userId' );
		$model		= new Model_Finance_Fund( $this->env );

		$fund		= $model->get( $fundId );
		if( !$fund || $fund->userId != $userId ){
			$this->messenger->noteError( $words->msgFundInvalid );
			$this->restart( NULL, TRUE );
		}
		
		if( $request->has( 'save' ) ){
			if( !strlen( trim( $request->get( 'title' ) ) ) )
				$this->messenger->noteError( $words->msgNoTitle );
			if( !strlen( trim( $request->get( 'pieces' ) ) ) )
				$this->messenger->noteError( $words->msgNoPieces);
			if( !$this->messenger->gotError() ){
				$data	= array(
					'kag'			=> trim( $request->get( 'kag' ) ),
					'title'			=> trim( $request->get( 'title' ) ),
					'pieces'		=> trim( $request->get( 'pieces' ) ),
					'modifiedAt'	=> time(),
				);
				$model->edit( $fundId, $data );
				$fund	= $model->get( $fundId );
				$this->messenger->noteSuccess( $words->msgSuccess, $fund->ISIN );
				$this->restart( NULL, TRUE );
			}
		}
		$this->addData( 'fund', $model->get( $fundId ) );
	}
	
	public function index(){
		$userId		= $this->env->getSession()->get( 'userId' );
		$modelFund	= new Model_Finance_Fund( $this->env );
		$modelPrice	= new Model_Finance_FundPrice( $this->env );
		$funds		= $modelFund->getAllByIndex( 'userId', $userId );
		foreach( $funds as $nr => $fund ){
			$empty	= (object) array( 'fundId' => $fund->fundId, 'price' => 0, 'timestamp' => 0 );
			$price	= $modelPrice->getAll(
				array( 'fundId' => $fund->fundId ),
				array( 'timestamp' => 'DESC' ),
				array( 0, 1 )
			);
			$funds[$nr]->price	= $price ? $price[0] : $empty;
		}
		$this->addData( 'funds', $funds );
	}
	
	public function remove(){}

	/**
	 *	Fetchs current fund price for ISIN by requesting Yahoo! Finance
	 *	@access		protected
	 *	@param		string		$fund		Fund data object
	 *	@return		float		Current price or 0 if request failed
	 */
	protected function fetchFromYahoo( $fund ){
		$words	= $this->getWords( 'request' );
		$url	= 'http://de.finance.yahoo.com/lookup?s=';
		try{
			$html		= Net_Reader::readUrl( $url.$fund->ISIN );
			$html		= substr( $html, strpos( $html, '<div id="yfi_sym_results">' ) + 26 );
			$html		= substr( $html, 0, strpos( $html, '</table>' ) + 8 );
			$html		= substr( $html, strpos( $html, '<tbody>' ) );
			$html		= substr( $html, 0, strpos( $html, '</tbody>' ) + 8 );
			$html		= preg_replace( '/\w+=\"[^"]+\"/', '', $html );
			$html		= preg_replace( '/&/', '&amp;', $html );
			$markets	= array( 'BER', 'HAM' );													//  preferred markets
			try{
				$xml	= new XML_Element( $html );
				$price	= 0;
				$prices	= array();
				foreach( $xml->tr as $row ){														//  iterate table rows
					$price	= str_replace( ',', '.', trim( (string) $row->td[3] ) );				//  extract price from 4th cell
					$prices[(string) $row->td[5]]	= $price;										//  extract market from 6th cell and store with price
				}
				foreach( $markets as $market )														//  itarate markets
					if( isset( $prices[$market] ) )													//  fund price found on market
						return $prices[$market];													//  return this price

				asort( $prices );																	//  otherwise sort fund prices ascending
				if( count( $prices ) )																//  several prices where found for fund
					return array_shift( array_values( $prices ) );									//  return lowest fund price
			}
			catch( Exception $e ){
				$this->messenger->noteError( $words->msgParsingFailed, $fund->ISIN );
			}
		}
		catch( Exception $e ){
			$this->messenger->noteError( $words->msgRequestFailed, $fund->ISIN );
		}
		return 0;																					//  return 0 as fallback value, indicating that the request failed
	}
	
	public function requestPrices(){
		$userId		= $this->env->getSession()->get( 'userId' );
		$words		= $this->getWords( 'request' );
		$modelFund	= new Model_Finance_Fund( $this->env );
		$modelPrice	= new Model_Finance_FundPrice( $this->env );
		$funds		= $modelFund->getAllByIndex( 'userId', $userId );
		foreach( $funds as $fund ){
			$price	= $modelPrice->getAll(
				array( 'fundId' => $fund->fundId ),
				array( 'timestamp' => 'DESC' ),
				array( 0, 1 )
			);
			$timestamp	= $price ? $price[0]->timestamp : 0;
			if( time() - $timestamp < 23 * 60 * 60 ){												//  23 hours: 1 hour open work time
				$this->messenger->noteNotice( $words->msgNoUpdate, $fund->ISIN );
				continue;
			}

			$price	= $this->fetchFromYahoo( $fund );
			if( $price ){
				$data	= array(
					'fundId'	=> $fund->fundId,
					'price'		=> $price,
					'pieces'	=> $fund->pieces,
					'timestamp'	=> time()
				);
				$modelPrice->add( $data );
				$this->messenger->noteSuccess( $words->msgSuccess, $fund->ISIN );
			}
			else
				$this->messenger->noteError( $words->msgNoPrices, $fund->ISIN );
		}
		$this->restart( NULL, TRUE );
	}
}
?>