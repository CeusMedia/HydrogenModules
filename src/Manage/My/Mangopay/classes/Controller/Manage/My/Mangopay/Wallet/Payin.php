<?php
class Controller_Manage_My_Mangopay_Wallet_Payin extends Controller_Manage_My_Mangopay_Abstract{

	protected function __onInit(){
		parent::__onInit();
		$this->addData( 'wordsCards', $this->getWords( 'cardTypes', 'manage/my/mangopay/card' ) );
		$this->addData( 'wallets', $this->logic->getUserWallets( $this->userId ) );
	}

	public function bank( $walletId = NULL ){
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
				if( ( $backwardTo = $this->request->get( 'backwardTo' ) ) )
					$this->restart( $backwardTo );
				if( ( $from = $this->request->get( 'from' ) ) )
					$this->restart( $from );
				$this->restart( 'manage/my/mangopay/wallet/view/'.$walletId );
			}
		}
		else if( $this->request->has( 'amount' ) && $this->request->get( 'currency' ) ){
			$amount	= $this->request->get( 'amount' );
		//	$amount	= $this->checkAmount( $amount, $this->currency );									//  @todo handle amount format and sanity
			try{
				$returnUrl		= $this->env->url.'manage/my/mangopay/wallet/payin/bank/'.$walletId;
				if( $from = $this->request->get( 'from' ) )
					$returnUrl	.= '?from=manage/my/mangopay/wallet/view/'.$walletId;
				$createdPayIn	= $this->logic->createBankPayInViaWeb(
					'SOFORT',//'GIROPAY',//'SOFORT'
					$this->userId,
					$walletId,
					$this->request->get( 'currency' ),
					round( $amount * 100 ),
					$returnUrl
				);
				$this->restart( $createdPayIn->ExecutionDetails->RedirectURL, FALSE, NULL, TRUE );
			}
			catch( Exception $e ){
				$this->handleMangopayResponseException( $e );
			}
		}
		$this->addData( 'walletId', $walletId );
	}

	public function card( $walletId, $amount = NULL ){
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
				if( $from = $this->request->get( 'from' ) )
					$this->restart( $from );
				$this->restart( './manage/my/mangopay/wallet/'.$walletId );
			}
		}
		else if( $this->request->has( 'amount' ) && $this->request->get( 'currency' ) ){
			$returnUrl		= $this->env->url.'manage/my/mangopay/wallet/payin/card/'.$walletId;
			if( $from = $this->request->get( 'from' ) )
				$returnUrl	.= '?from=manage/my/mangopay/wallet/view/'.$walletId;
			$createdPayIn	= $this->logic->createCardPayInViaWeb(
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
		$this->addData( 'amount', $amount );
	}

	public function index( $walletId, $type = NULL ){
		$wallet		= $this->checkWalletIsOwn( $walletId );
		$this->addData( 'walletId', $walletId );
		$this->addData( 'wallet', $wallet );
		$this->addData( 'type', $type );
	}
}
?>
