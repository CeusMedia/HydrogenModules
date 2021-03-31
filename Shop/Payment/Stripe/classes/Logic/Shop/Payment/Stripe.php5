<?php
class Logic_Shop_Payment_Stripe extends CMF_Hydrogen_Environment_Resource_Logic
{
	protected $logicStripe;
	protected $logicShop;
	protected $modelPayin;
	protected $modelPayment;
	protected $session;

	public function notePayment( $source, $stripeUserId, $orderId )
	{
		$paymentId	= $this->modelPayment->add( array(
			'orderId'		=> $orderId,
			'userId'		=> $stripeUserId,
			'payInId'		=> $source->id,
			'object'		=> json_encode( $source ),
			'status'		=> 0,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		) );
		$this->logicShop->setOrderPaymentId( $orderId, $paymentId );
		$this->session->set( 'shop_payment_stripe_id', $paymentId );
		$this->session->set( 'shop_payment_stripe_sourceId', $source->id );
		return $paymentId;
	}

	public function updatePayment( $source )
	{
		$payment	= $this->modelPayment->getByIndex( 'payInId', $source->id );
		if( $source->redirect->status === "succeeded" )
			$status	= Model_Shop_Payment_Stripe::STATUS_SUCCEEDED;
		else if( $source->redirect->status === "failed" )
			$status	= Model_Shop_Payment_Stripe::STATUS_FAILED;
		else
			return 0;
		return $this->modelPayment->edit( $payment->paymentId, array(
			'status'		=> (int) $status,
			'object'		=> json_encode( $source ),
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
		$clientSellerId	= $this->logicStripe->getUserIdFromLocalUserId( 0 );
		if( $clientSellerId ){
//			remark( 'Client Seller ID:' );
//			print_m( $clientSellerId );
			$clientSellerWallets	= $this->logicStripe->getUserWalletsByCurrency(
				$clientSellerId,
				$order->currency
			);
			if( $clientSellerWallets ){
				$paymentType	= $payIn->PaymentType;
				if( $paymentType === "CARD" )
					$paymentType	= $payIn->PaymentDetails->CardType;
				if( $paymentType === "DIRECT_DEBIT" )
					$paymentType	= $payIn->PaymentDetails->DirectDebitType;
				$buyerId	= $this->logicStripe->getUserIdFromLocalUserId( $order->userId );
				$buyer		= $this->logicStripe->getUser( $buyerId );
				$fees		= $this->logicStripe->calculateFeesForPayIn(
					$order->priceTaxed * 100,
					$order->currency,
					$paymentType
				);
//				remark( 'Client Seller Wallet:' );
//				print_m( $clientSellerWallets );
				$result	= $this->logicStripe->transfer(
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
		$this->logicStripe		= new Logic_Payment_Stripe( $this->env );
		$this->logicShop		= new Logic_Shop( $this->env );
		$this->modelPayins		= new Model_Stripe_Payin( $this->env );
		$this->modelPayment		= new Model_Shop_Payment_Stripe( $this->env );
		$this->session			= $this->env->getSession();
	}

	protected function getWalletForOrder( $stripeUserId, $orderCurrency )
	{
		$wallets		= $this->logicStripe->getUserWalletsByCurrency( $stripeUserId, $orderCurrency );
		if( !$wallets )
			$wallets	= array( $this->logicStripe->createUserWallet( $stripeUserId, $orderCurrency ) );
		$wallet	= $wallets[0];
	}
}
