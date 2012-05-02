<?php
class Controller_Work_Finance_Bank_Account extends CMF_Hydrogen_Controller{

	/**	@var	CMF_Hydrogen_Environment_Resource_Messenger		$messenger		Shortcut to messenger object */
	protected $messenger;

	protected function __onInit(){
		$this->messenger	= $this->env->getMessenger();
	}

	protected function getBanksWithAccounts(){
		$userId			= $this->env->getSession()->get( 'userId' );
		$modelBank		= new Model_Finance_Bank( $this->env );
		$modelAccount	= new Model_Finance_Bank_Account( $this->env );
		$banks			= $modelBank->getAllByIndex( 'userId', $userId );
		foreach( $banks as $nr => $bank ){
			$accounts	= $modelAccount->getAllByIndex( 'bankId', $bank->bankId );
			$banks[$nr]->accounts	= $accounts;
		}
		return $banks;
	}
	public function add(){
		$request		= $this->env->getRequest();
		$words			= $this->getWords( 'add' );
		$userId			= $this->env->getSession()->get( 'userId' );
		$modelAccount	= new Model_Finance_Bank_Account( $this->env );
		$modelBank		= new Model_Finance_Bank( $this->env );

		if( $request->has( 'add' ) ){
			if( !strlen( trim( $request->get( 'title' ) ) ) )
				$this->messenger->noteError( $words->msgNoTitle );
			if( !$this->messenger->gotError() ){
				$value	= trim( $request->get( 'value' ) );
				if( substr_count( $value, ',' ) && substr_count( $value, '.' ) )
					$value	= str_replace( '.', '', $value );
				$value	= str_replace( ',', '.', $value );
						
				$data	= array(
					'bankId'		=> $request->get( 'bankId' ),
					'type'			=> trim( $request->get( 'type' ) ),
					'currency'		=> trim( $request->get( 'currency' ) ),
					'title'			=> trim( $request->get( 'title' ) ),
					'accountKey'	=> trim( $request->get( 'accountKey' ) ),
					'value'			=> $value,
					'timestamp'		=> time(),
				);
				$bankAccountId	= $modelAccount->add( $data );
				$account	= $modelAccount->get( $account );
				$this->messenger->noteSuccess( $words->msgSuccess, $account->title );
				$this->restart( NULL, TRUE );
			}
		}
		$account	= (object) array();
		foreach( $modelAccount->getColumns() as $column )
			$account->$column	= $request->has( $column ) ? $request->get( $column ) : '';
		$this->addData( 'account', $account );
		$this->addData( 'banks', $modelBank->getAllByIndex( 'userId', $userId ) );
	}
	
	public function edit( $bankAccountId ){
		$request		= $this->env->getRequest();
		$words			= $this->getWords( 'edit' );
		$userId			= $this->env->getSession()->get( 'userId' );
		$modelBank		= new Model_Finance_Bank( $this->env );
		$modelAccount	= new Model_Finance_Bank_Account( $this->env );
		$account		= $modelAccount->get( $bankAccountId );
		if( !$account ){
			$this->messenger->noteError( $words->msgAccountInvalid );
			$this->restart( NULL, TRUE );
		}

		$bank		= $modelBank->get( $account->bankId );
		if( !$bank || $bank->userId != $userId ){
			$this->messenger->noteError( $words->msgAccountInvalid );
			$this->restart( NULL, TRUE );
		}
		
		if( $request->has( 'save' ) ){
			if( !strlen( trim( $request->get( 'title' ) ) ) )
				$this->messenger->noteError( $words->msgNoTitle );
			if( !$this->messenger->gotError() ){
				$value	= trim( $request->get( 'value' ) );
				if( substr_count( $value, ',' ) && substr_count( $value, '.' ) )
					$value	= str_replace( '.', '', $value );
				$value	= str_replace( ',', '.', $value );
				$data	= array(
					'bankId'		=> $request->get( 'bankId' ),
					'type'			=> trim( $request->get( 'type' ) ),
					'currency'		=> trim( $request->get( 'currency' ) ),
					'title'			=> trim( $request->get( 'title' ) ),
					'accountKey'	=> trim( $request->get( 'accountKey' ) ),
					'value'			=> $value,
					'timestamp'		=> time(),
				);
				$modelAccount->edit( $bankAccountId, $data );
				$account	= $modelAccount->get( $bankAccountId );
				$this->messenger->noteSuccess( $words->msgSuccess, $account->title );
				$this->restart( NULL, TRUE );
			}
		}
		$this->addData( 'account', $account );
		$this->addData( 'banks', $modelBank->getAllByIndex( 'userId', $userId ) );
	}
	
	public function index(){
		$userId			= $this->env->getSession()->get( 'userId' );
		$modelBank		= new Model_Finance_Bank( $this->env );
		$modelAccount	= new Model_Finance_Bank_Account( $this->env );

		$banks		= array();
		foreach( $modelBank->getAllByIndex( 'userId', $userId ) as $bank )
			$banks[$bank->bankId]	= $bank;
		
		$accounts	= $modelAccount->getAllByIndex( 'bankId', array_keys( $banks ) );
		foreach( $accounts as $nr => $account ){
			$accounts[$nr]->bank	= $banks[$account->bankId];
		}
		$this->addData( 'banks', $banks );
		$this->addData( 'accounts', $accounts );
	}
	
	public function update(){
		$count			= 0;
		$banks			= $this->getBanksWithAccounts();
		$modelAccount	= new Model_Finance_Bank_Account( $this->env );
		try{
			$clock	= new Alg_Time_Clock();
			foreach( $banks as $bankId => $bank ){

				$reader	= NULL;
				switch( $bank->type ){
					case 'Postbank':
		#				@unlink( $banks->cacheFile ); 
						$reader	= new Model_Finance_Bank_Account_Reader_Postbank( $bank );
						break;
		#			case 'DeutscheBank':
		#				$reader	= new Model_Finance_Bank_Account_Reader_DeutscheBank( $account );
		#				break;
					case 'DKB':
		#				@unlink( $banks->cacheFile ); 
		#				$reader	= new Model_Finance_Bank_Account_Reader_DKB( $bank );
						break;
		#			case 'DWS':
		#				@unlink( $banks->cacheFile ); 
		#				$reader	= new Model_Finance_Bank_Account_Reader_DWS( $bank );
						break;
		#			default:
		#				remark( 'Bank "'.$bank->type.'" wird nicht unterstützt.' );
				}
				if( $reader ){
					$values	= $reader->getAccountValues();
					foreach( $bank->accounts as $bankAccount ){
						if( array_key_exists( $bankAccount->accountKey, $values ) ){
							$count ++;
							$value	= (float) $values[$bankAccount->accountKey];
							$data	= array( 'value' => $value, 'timestamp' => time() );
							$modelAccount->edit( $bankAccount->bankAccountId, $data );
							$this->messenger->noteSuccess( 'Konto "'.$bankAccount->title.'" wurde aktualisiert.' );
						}
					}
				}
			}
		#	print_m( $banks );
#			remark( $clock->stop( 3, 1 ).'ms' );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage() );
#			UI_HTML_Exception_Page::display( $e );
#			exit;
		}
		$this->restart( NULL, TRUE );
	}
}

?>