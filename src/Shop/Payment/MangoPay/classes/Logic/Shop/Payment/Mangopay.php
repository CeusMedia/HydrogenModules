<?php

use CeusMedia\HydrogenFramework\Environment\Resource\Logic;

class Logic_Shop_Payment_Mangopay extends Logic
{
	protected Logic_Payment_Mangopay $logicMangopay;
	protected Logic_Shop $logicShop;
	protected Model_Mangopay_Payin $modelPayin;
	protected Model_Shop_Payment_Mangopay $modelPayment;
	protected $session;

	public function notePayment( object $payIn, int|string $mangopayUserId, int|string $orderId ): string
	{
		$paymentId	= $this->modelPayment->add( array(
			'orderId'		=> $orderId,
			'userId'		=> $mangopayUserId,
			'payInId'		=> $payIn->Id,
			'object'		=> json_encode( $payIn ),
			'status'		=> 0,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		) );
		$this->logicShop->setOrderPaymentId( $orderId, $paymentId );
		$this->session->set( 'shop_payment_mangopay_id', $paymentId );
		$this->session->set( 'shop_payment_mangopay_payInId', $payIn->Id );
		return $paymentId;
	}

	public function updatePayment( int|string $payIn ): int
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

	public function transferOrderAmountToClientSeller( int|string $orderId, object $payIn, bool $strict = TRUE ): ?bool
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

	protected function __onInit(): void
	{
		$this->logicShop		= new Logic_Shop( $this->env );
		$this->logicMangopay	= new Logic_Payment_Mangopay( $this->env );
		$this->modelPayment		= new Model_Shop_Payment_Mangopay( $this->env );
		$this->modelPayin		= new Model_Mangopay_Payin( $this->env );
		$this->session			= $this->env->getSession();
	}

	protected function getWalletForOrder( int|string $mangopayUserId, $orderCurrency )
	{
		$wallets		= $this->logicMangopay->getUserWalletsByCurrency( $mangopayUserId, $orderCurrency );
		if( !$wallets )
			$wallets	= [$this->logicMangopay->createUserWallet( $mangopayUserId, $orderCurrency )];
		$wallet	= $wallets[0];
	}
}
