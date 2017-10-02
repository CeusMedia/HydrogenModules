<?php
class Controller_Manage_My_Mangopay_Bank extends Controller_Manage_My_Mangopay{

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
				print_m( $created );
				die;
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

	public function payIn( $bankAccountId ){
		$bankAccount	= $this->logic->getBankAccount( $this->userId, $bankAccountId );
		$fees			= $this->moduleConfig->getAll( 'fees.payin.' );
		$this->saveBackLink( 'from', 'payin_from' );
		if( $this->request->has( 'save' ) ){
			$walletId		= $this->request->get( 'walletId' );
			$wallet			= $this->checkWalletIsOwn( $walletId, 'redirectUrl' );						//  @todo handle invalid walled
			try{
				$createdPayIn	= $this->logic->createPayInFromBankAccount(
					$this->userId,
					$walletId,
					$bankAccountId,
					round( $this->request->get( 'amount' ) * 100 )
				);
	print_m( $bankAccount );
	print_m( $wallet );
	print_m( $createdPayIn );
	die;
				$this->handleStatus( $createdPayIn, $bankAccount, $wallet );
				throw new RuntimeException( 'Not implemented yet' );
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
		$this->addData( 'wallets', $wallets );
		$this->addData( 'bankAccountId', $bankAccountId );
		$this->addData( 'bankAccount', $bankAccount );
		$this->addData( 'from', $this->request->get( 'from' ) );
	}

	protected function handleStatus( $payIn, $bankAccount, $wallet ){
		$price	= View_Manage_My_Mangopay::formatMoney( $payIn->DebitedFunds );
print_m( $payIn );die;
		if( $payIn->Status === \MangoPay\PayInStatus::Failed ){
			$this->handleErrorCode( $payIn->ResultCode );

			if( ( $from = $this->request->get( 'from' ) ) )
				$this->restart( $from );
			$this->restart( 'payin/'.$card->Id, TRUE );
		}
		else if( $payIn->Status === \MangoPay\PayInStatus::Created ){
			$this->session->set( 'payInId', $payIn->Id );
//				print_m( $createdPayIn );die;
			header( 'Location: '.$payIn->ExecutionDetails->SecureModeRedirectURL );
			exit;
		}
		$this->cache->remove( 'user_'.$this->userId.'_wallets' );
		$this->cache->remove( 'user_'.$this->userId.'_transactions' );
		$this->messenger->noteSuccess( 'Payed <strong>%s</strong> into Wallet <strong>%s</strong>.', $price, $wallet->Description );
		$this->followBackLink( 'payin_from' );
		$this->restart( '..', TRUE );
	}


	public function payOut( $bankAccountId ){
		if( $this->request->has( 'save' ) ){
			throw new RuntimeException( 'Not implemented yet' );
		}
	}

	public function index(){
		$pagination	= new \MangoPay\Pagination();
		$sorting	= new \MangoPay\Sorting();
		$sorting->AddField( 'CreationDate', 'DESC' );
		$this->addData( 'bankAccounts', $this->mangopay->Users->GetBankAccounts( $this->userId, $pagination, $sorting ));
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
