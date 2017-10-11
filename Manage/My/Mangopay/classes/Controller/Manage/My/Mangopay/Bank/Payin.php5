<?php
/**
 *	@todo   			migrate index to add and implement payin history on index
 */
class Controller_Manage_My_Mangopay_Bank_Payin extends Controller_Manage_My_Mangopay_Abstract{

	protected $words;

	public function __onInit(){
		parent::__onInit();
//		$this->words			= $this->getWords( 'add', 'manage/my/mangopay/bank/payin' );
		$this->sessionPrefix	= 'manage_my_mangopay_bank_payin_';
	}

	public function index( $bankAccountId, $walletId = NULL, $amount = NULL ){
		$bankAccount	= $this->checkIsOwnBankAccount( $bankAccountId );
		$walletId = $walletId ? $walletId : $this->request->get( 'walletId' );
		if( $walletId )
			$wallet	= $this->checkWalletIsOwn( $walletId, 'redirectUrl' );

		$fees			= $this->moduleConfig->getAll( 'fees.payin.' );
		$this->saveBackLink( 'from', 'from' );									//  @todo kriss: may be earlier?
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
				$this->addData( 'from', $this->session->get( $this->sessionPrefix.'from' ) );
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
}
