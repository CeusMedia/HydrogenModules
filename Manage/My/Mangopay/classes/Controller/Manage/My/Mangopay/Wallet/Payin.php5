<?php
class Controller_Manage_My_Mangopay_Wallet_Payin extends Controller_Manage_My_Mangopay{

	protected function __onInit(){
		parent::__onInit();
		$this->addData( 'wordsCards', $this->getWords( 'cardTypes', 'manage/my/mangopay/card' ) );
		$this->addData( 'wallets', $this->logic->getUserWallets( $this->userId ) );
	}

	public function bankwire( $walletId = NULL ){
		$walletId	= $walletId ? $walletId : $this->request->get( 'walletId' );
		$wallet		= $this->checkWalletIsOwn( $walletId );
		if( $this->request->has( 'amount' ) && $this->request->get( 'currency' ) ){
			$payIn		= new \MangoPay\PayIn();
			$payIn->CreditedWalletId		= $walletId;
			$payIn->AuthorId				= $this->userId;											//  @todo inset user ID from session

			$amount	= $this->request->get( 'amount' );
		//	$amount	= $this->checkAmount( $amount, $this->currency );									//  @todo handle amount format and sanity

			// payment type as CARD
			$payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsBankWire();
			$payIn->PaymentDetails->DeclaredDebitedFunds			= new \MangoPay\Money();
			$payIn->PaymentDetails->DeclaredDebitedFunds->Amount	= $amount;
			$payIn->PaymentDetails->DeclaredDebitedFunds->Currency	= $this->currency;
			$payIn->PaymentDetails->DeclaredFees					= new \MangoPay\Money();
			$payIn->PaymentDetails->DeclaredFees->Amount			= $amount * $this->factorFees;
			$payIn->PaymentDetails->DeclaredFees->Currency			= $this->currency;
			$payIn->ExecutionDetails	= new \MangoPay\PayInExecutionDetailsDirect();
			$createdPayIn = $this->mangopay->PayIns->Create( $payIn );
			$this->addData( 'payin', $createdPayIn );
			$this->addData( 'amount', $amount );
			$this->addData( 'currency', $this->currency );
		}
	}

	public function card( $walletId = NULL, $cardId = NULL ){
		$walletId	= $walletId ? $walletId : $this->request->get( 'walletId' );
		$wallet		= $this->checkWalletIsOwn( $walletId );
		$cardId		= $cardId ? $cardId : $this->request->get( 'cardId' );
		if( $cardId ){
			$this->checkIsOwnCard( $cardId );
			$parameters	= http_build_query( array(
				'from'			=> 'manage/my/mangopay/wallet/view/'.$walletId
			), '', '&' );
			$url		= './manage/my/mangopay/card/payin/preAuthorized/%s/%s?%s';
			$this->restart( sprintf( $url, $cardId, $walletId, $parameters ) );
		}
		$pagination	= new \MangoPay\Pagination();
		$sorting	= new \MangoPay\Sorting();
		$sorting->AddField( 'CreationDate', 'DESC' );
		$cards	= $this->mangopay->Users->GetCards( $this->userId, $pagination, $sorting );
		$this->addData( 'cards', $cards );
		$this->addData( 'walletId', $walletId );
	}

	public function cardWeb( $walletId ){
		$walletId	= $walletId ? $walletId : $this->request->get( 'walletId' );
		$wallet		= $this->checkWalletIsOwn( $walletId );
		if( $this->request->has( 'transactionId' ) ){
			$result = $this->mangopay->PayIns->Get( $this->request->get( 'transactionId' ) );

			if( $result->Status === "SUCCEEDED" ){
				$this->messenger->noteSuccess( 'Payin succeeded.' );
				$this->restart( './manage/my/mangopay/wallet/'.$walletId );
			}
			else{
				$helper	= new View_Helper_Mangopay_Error( $this->env );
				$helper->setCode( $result->ResultCode );
				$this->messenger->noteError( $helper->render() );
				$this->restart( './manage/my/mangopay/wallet/'.$walletId );
			}
		}
		else if( $this->request->has( 'amount' ) && $this->request->get( 'currency' ) ){
			$returnUrl		= $this->env->url.'manage/my/mangopay/wallet/payin/cardWeb/'.$walletId;
			$createdPayIn	= $this->logic->createPayInFromCardViaWeb(
				$this->userId,
				$walletId,
				$this->request->get( 'cardType' ),
				$this->request->get( 'currency' ),
				round( $this->request->get( 'amount' ) * 100 ),
				$returnUrl
			);
			$this->restart( $createdPayIn->ExecutionDetails->RedirectURL, FALSE, NULL, TRUE );
		}
		$this->addData( 'walletId', $walletId );
	}

	protected function checkWallet( $walletId ){
		try{
			$wallet	= $this->mangopay->Wallets->Get( $walletId );
			return $wallet;
		}
		catch( Exception $e ){
//			$this->messenger->noteNotice( "Exception: ".$e->getMessage( ) );
			$this->messenger->noteError( "Invalid wallet ID given." );
			$this->restart( NULL, TRUE );
		}
	}

	protected function checkWalletIsOwn( $walletId ){
		$wallet		= $this->checkWallet( $walletId );
		//	@todo check against list of user wallets
		return $wallet;
	}


	public function index( $walletId, $type = NULL ){
		$wallet		= $this->checkWalletIsOwn( $walletId );
		$this->addData( 'walletId', $walletId );
		$this->addData( 'wallet', $wallet );
		$this->addData( 'type', $type );
	}
}
?>
