<?php
class Controller_Work_Finance_Bank extends CMF_Hydrogen_Controller{

	/**	@var	CMF_Hydrogen_Environment_Resource_Messenger		$messenger		Shortcut to messenger object */
	protected $messenger;

	public function onInit(){
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
		$request	= $this->env->getRequest();
		$words		= $this->getWords( 'add' );
		$userId		= $this->env->getSession()->get( 'userId' );
		$model		= new Model_Finance_Bank( $this->env );

		if( $request->has( 'add' ) ){
			if( !strlen( trim( $request->get( 'title' ) ) ) )
				$this->messenger->noteError( $words->msgNoTitle );
#			if( !strlen( trim( $request->get( 'username' ) ) ) )
#				$this->messenger->noteError( $words->msgNoUsername);
#			if( !strlen( trim( $request->get( 'password' ) ) ) )
#				$this->messenger->noteError( $words->msgNoPassword);
			if( !$this->messenger->gotError() ){
				$data	= array(
					'userId'		=> $userId,
					'type'			=> trim( $request->get( 'type' ) ),
					'username'		=> trim( $request->get( 'username' ) ),
					'password'		=> trim( $request->get( 'password' ) ),
					'title'			=> trim( $request->get( 'title' ) ),
					'createdAt'		=> time(),
				);
				$bankId	= $model->add( $data );
				$bank	= $model->get( $bank );
				$this->messenger->noteSuccess( $words->msgSuccess, $bank->title );
				$this->restart( NULL, TRUE );
			}
		}
		$bank	= (object) array();
		foreach( $model->getColumns() as $column )
			$bank->$column	= $request->has( $column ) ? $request->get( $column ) : '';
		$this->addData( 'bank', $bank );
	}
	
	public function edit( $bankId ){
		$request	= $this->env->getRequest();
		$words		= $this->getWords( 'edit' );
		$userId		= $this->env->getSession()->get( 'userId' );
		$model		= new Model_Finance_Bank( $this->env );

		$bank		= $model->get( $bankId );
		if( !$bank || $bank->userId != $userId ){
			$this->messenger->noteError( $words->msgBankInvalid );
			$this->restart( NULL, TRUE );
		}
		
		if( $request->has( 'save' ) ){
			if( !strlen( trim( $request->get( 'title' ) ) ) )
				$this->messenger->noteError( $words->msgNoTitle );
#			if( !strlen( trim( $request->get( 'username' ) ) ) )
#				$this->messenger->noteError( $words->msgNoUsername);
#			if( !strlen( trim( $request->get( 'password' ) ) ) )
#				$this->messenger->noteError( $words->msgNoPassword);
			if( !$this->messenger->gotError() ){
				$data	= array(
					'userId'		=> $userId,
					'type'			=> trim( $request->get( 'type' ) ),
					'username'		=> trim( $request->get( 'username' ) ),
					'password'		=> trim( $request->get( 'password' ) ),
					'title'			=> trim( $request->get( 'title' ) ),
					'modifiedAt'	=> time(),
				);
				$model->edit( $bankId, $data );
				$bank	= $model->get( $bankId );
				$this->messenger->noteSuccess( $words->msgSuccess, $bank->title );
				$this->restart( NULL, TRUE );
			}
		}
		$this->addData( 'bank', $model->get( $bankId ) );
	}
	
	public function index(){
		$this->addData( 'banks', $this->getBanksWithAccounts() );
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