<?php
class Logic_Shop_Payment_Mangopay extends CMF_Hydrogen_Environment_Resource_Logic
{
	protected $logicMangopay;
	protected $logicShop;
	protected $modelPayin;
	protected $modelPayment;
	protected $session;

	public function notePayment( $payIn, $mangopayUserId, $orderId )
	{
		$paymentId	= $this->modelPayment->add( array(
			'orderId'		=> $orderId,
			'userId'		=> $mangopayUserId,
			'payInId'		=> $payIn->Id,
			'object'		=> json_encode( $payIn ),
			'status'		=> 0,
			'createdAt'		=> time(),
			'createdAt'		=> time(),
		) );
		$this->logicShop->setOrderPaymentId( $orderId, $paymentId );
		$this->session->set( 'shop_payment_mangopay_id', $paymentId );
		$this->session->set( 'shop_payment_mangopay_payInId', $payIn->Id );
		return $paymentId;
	}

	public function updatePayment( $payIn )
	{
		$payment	= $this->modelPayment->getByIndex( 'payInId', $payIn->Id );
		if( !$payment )
			throw new DomainException( 'No payment found for pay in ID' );
		if( $payIn->Status === "SUCCEEDED" )
			$status	= Model_Shop_Payment_Mangopay::STATUS_SUCCEEDED;
		else if( $payIn->Status === "FAILED" )
			$status	= Model_Shop_Payment_Mangopay::STATUS_FAILED;
		else
			return 0;
		return $this->modelPayment->edit( $payment->paymentId, array(
			'status'		=> (int) $status,
			'object'		=> json_encode( $payIn ),
		 	'modifiedAt'	=> time(),
		) );
	}

	public function transferOrderAmountToClientSeller( $orderId, $payIn, bool $strict = TRUE )
	{
		$order		= $this->logicShop->getOrder( $orderId );
		if( !$order )
			throw new RangeException( 'Invalid order ID' );
//		remark( 'Order:' );
//		print_m( $order );
		$clientSellerId	= $this->logicMangopay->getUserIdFromLocalUserId( 0 );
		if( $clientSellerId ){
//			remark( 'Client Seller ID:' );
//			print_m( $clientSellerId );
			$clientSellerWallets	= $this->logicMangopay->getUserWalletsByCurrency(
				$clientSellerId,
				$order->currency
			);
			if( $clientSellerWallets ){
				$paymentType	= $payIn->PaymentType;
				if( $paymentType === "CARD" )
					$paymentType	= $payIn->PaymentDetails->CardType;
				if( $paymentType === "DIRECT_DEBIT" )
					$paymentType	= $payIn->PaymentDetails->DirectDebitType;
				$buyerId	= $this->logicMangopay->getUserIdFromLocalUserId( $order->userId );
				$buyer		= $this->logicMangopay->getUser( $buyerId );
				$fees		= $this->logicMangopay->calculateFeesForPayIn(
					$order->priceTaxed * 100,
					$order->currency,
					$paymentType
				);
//				remark( 'Client Seller Wallet:' );
//				print_m( $clientSellerWallets );
				$result	= $this->logicMangopay->transfer(
					$payIn->CreditedUserId,
					$clientSellerId,
					$payIn->CreditedWalletId,
					$clientSellerWallets[0]->Id,
					$order->currency,
					$order->priceTaxed * 100,
					$fees
				);
				return TRUE;
			}
		}
		return NULL;
	}

	protected function __onInit()
	{
		$this->logicMangopay	= new Logic_Payment_Mangopay( $this->env );
		$this->logicShop		= new Logic_Shop( $this->env );
		$this->modelPayins		= new Model_Mangopay_Payin( $this->env );
		$this->modelPayment		= new Model_Shop_Payment_Mangopay( $this->env );
		$this->session			= $this->env->getSession();
	}

	protected function getWalletForOrder( $mangopayUserId, $orderCurrency )
	{
		$wallets		= $this->logicMangopay->getUserWalletsByCurrency( $mangopayUserId, $orderCurrency );
		if( !$wallets )
			$wallets	= array( $this->logicMangopay->createUserWallet( $mangopayUserId, $orderCurrency ) );
		$wallet	= $wallets[0];
	}
}
