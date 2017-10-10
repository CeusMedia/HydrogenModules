<?php
class Controller_Manage_My_Mangopay_Bank extends Controller_Manage_My_Mangopay_Abstract{

	protected $words;

	public function __onInit(){
		parent::__onInit();
		$this->words			= $this->getWords( 'add', 'manage/my/mangopay/card' );
		$this->sessionPrefix	= 'manage_my_mangopay_bank_';
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			try{
				$created	= $this->logic->createBankAccount(
					$this->userId,
					$this->request->get( 'iban' ),
					$this->request->get( 'bic' ),
					$this->request->get( 'title' )
				);
				$this->restart( 'view/'.$created->Id, TRUE );
			}
			catch( Exception $e ){
				$this->handleMangopayResponseException( $e );
			}
//			$this->logic->createBankAccount();
//			throw new RuntimeException( 'Not implemented yet' );
		}
		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );
	}

	public function deactivate( $bankAccountId ){
		if( $this->request->getMethod() === "POST" ){									//  form has been executed
			$password		= $this->request->get( 'password' );
			$localUserId	= $this->session->get( 'userId' );
			$logicAuth		= $this->env->logic->authentication;
			if( $logicAuth->checkPassword( $localUserId, $password ) ){
				try{
					$this->logic->deactivateBankAccount( $this->userId, $bankAccountId );
					$this->restart( NULL, TRUE );
				}
				catch( Exception $e ){
					$this->handleMangopayResponseException( $e );
				}
			}
			else
				$this->messenger->noteError( 'Invalid password.' );
		}
		$this->restart( 'view/'.$bankAccountId, TRUE );

	}

	public function payIn( $bankAccountId, $walletId = NULL, $amount = NULL ){
		$bankAccount	= $this->checkIsOwnBankAccount( $bankAccountId );
		$walletId = $walletId ? $walletId : $this->request->get( 'walletId' );
		if( $walletId )
			$wallet	= $this->checkWalletIsOwn( $walletId, 'redirectUrl' );

		$fees			= $this->moduleConfig->getAll( 'fees.payin.' );
		$this->saveBackLink( 'from', 'payin_from' );									//  @todo kriss: may be earlier?
		if( $this->request->getMethod() === "POST" ){									//  form has been executed
			$walletId		= $this->request->get( 'walletId' );
			$wallet			= $this->checkWalletIsOwn( $walletId );						//  @todo handle invalid walled
			try{
				$createdPayIn	= $this->logic->createPayInFromBankAccount(
					$this->userId,
					$walletId,
					$bankAccountId,
					round( $this->request->get( 'amount' ) * 100 )
				);
				$this->addData( 'bankAccount', $bankAccount );
				$this->addData( 'wallet', $wallet );
				$this->addData( 'payin', $createdPayIn );
			}
			catch( MangoPay\Libraries\ResponseException $e ){
				$this->handleMangopayResponseException( $e );
			}
			catch( Exception $e ){
				UI_HTML_Exception_Page::display( $e );
				exit;
			}
		}
		$wallets		= $this->logic->getUserWallets( $this->userId );
		$this->addData( 'walletId', $walletId );
		$this->addData( 'wallets', $wallets );
		$this->addData( 'bankAccountId', $bankAccountId );
		$this->addData( 'bankAccount', $bankAccount );
		$this->addData( 'from', $this->request->get( 'from' ) );
		$this->addData( 'amount', $amount );
	}

	public function payOut( $bankAccountId ){
		if( $this->request->has( 'save' ) ){
			throw new RuntimeException( 'Not implemented yet' );
		}
	}

	public function index(){
		$this->addData( 'bankAccounts', $this->logic->getBankAccounts( $this->userId ) );
	}

	public function view( $bankAccountId ){
		try{
			$bankAccount	= $this->logic->getBankAccount( $this->userId, $bankAccountId );
			$this->addData( 'bankAccountId', $bankAccountId );
			$this->addData( 'bankAccount', $bankAccount );
			$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
			$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );
		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteError( 'Invalid User ID' );
			$this->restart( NULL, TRUE );
		}

	}

	protected function saveBackLink( $requestKey, $sessionKey ){
		$from = $this->request->get( $requestKey );
		if( $from )
			$this->session->set( $this->sessionPrefix.$sessionKey, $from );
	}
}
