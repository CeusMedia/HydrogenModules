<?php
class Job_Shop_Payment_Stripe extends Job_Abstract
{
	protected $logicStripe;
	protected $logicShop;
	protected $modelEvent;
	protected $modelStripePayin;
	protected $modelShopPayin;
	protected $backends				= array();

	public function handle()
	{
		$this->handleFailedBankWirePayIns();
		$this->handleSucceededBankWirePayIns();
	}

	/**
	 *	Register a payment backend.
	 *	@access		public
	 *	@param		string		$backend		...
	 *	@param		string		$key			...
	 *	@param		string		$title			...
	 *	@param		string		$path			...
	 *	@param		integer		$priority		...
	 *	@param		string		$icon			...
	 *	@param		array		$countries		...
	 *	@return		void
	 */
	public function registerPaymentBackend( $backend, string $key, string $title, string $path, int $priority = 5, string $icon = NULL, array $countries = array() )
	{
		$this->backends[]	= (object) array(
			'backend'	=> $backend,
			'key'		=> $key,
			'title'		=> $title,
			'path'		=> $path,
			'priority'	=> $priority,
			'icon'		=> $icon,
			'countries'	=> $countries,
		);
	}

	protected function __onInit()
	{
		$this->logicStripe			= new Logic_Shop_Payment_Stripe( $this->env );
		$this->logicShop			= new Logic_Shop( $this->env );
		$this->modelEvent			= new Model_Stripe_Event( $this->env );
		$this->modelStripePayin		= new Model_Stripe_Payin( $this->env );
		$this->modelShopPayin		= new Model_Shop_Payment_Stripe( $this->env );
		$this->moduleConfig			= $this->env->getConfig()->getAll( 'module.shop.', TRUE );

		$captain	= $this->env->getCaptain();
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, array() );
	}

	protected function handleFailedBankWirePayIns()
	{
		$logic		= Logic_Mail::getInstance( $this->env );
		$orders		= array( 'paymentId' => 'ASC' );
		$indices	= array(
			'status'	=> Model_Shop_Payment_Stripe::STATUS_CREATED,
		 	'object'	=> '%"BANK_WIRE"%',
		);
		$openShopBankWirePayments	= array();
		foreach( $this->modelShopPayin->getAll( $indices, $orders ) as $payment )
			$openShopBankWirePayments[$payment->payInId]	= $payment;

		$failedStripeBankWirePayments	= $this->modelStripePayin->getAll( array(
			'status'		=> Model_Stripe_Payin::STATUS_FAILED,								//  only failed payins
			'type'			=> Model_Stripe_Payin::TYPE_BANK_WIRE,							//  only bankwire payins
			'id'			=> array_keys( $openShopBankWirePayments ),							//  only for open shop payments
//			'modifiedAt'	=> '> '.( time() - 60 ),
		) );
		foreach( $failedStripeBankWirePayments as $payment ){
			$shopPayment	= $openShopBankWirePayments[$payment->id];
			$payIn			= json_decode( $payment->data );
			$payIn			= $payIn->failed;
			if( $shopPayment->status == Model_Shop_Payment_Stripe::STATUS_CREATED ){
				$order	= $this->logicShop->getOrder( $shopPayment->orderId );
				$this->logicStripe->updatePayment( $payIn );
				$this->logicShop->setOrderStatus( $shopPayment->orderId, Model_Shop_Order::STATUS_NOT_PAYED );

				$data		= array(
					'orderId'			=> $shopPayment->orderId,
					'paymentBackends'	=> $this->backends,
				);
				$logic->handleMail(
					new Mail_Shop_Customer_NotPayed( $this->env, $data ),
					$this->logicShop->getOrderCustomer( $order->orderId ),
					'de'
				);
				$logic->handleMail(
					new Mail_Shop_Manager_NotPayed( $this->env, $data ),
					(object) array( 'email' => $this->moduleConfig->get( 'mail.manager' ) ),
					'de'
				);
			}
		}
	}

	protected function handleSucceededBankWirePayIns()
	{
		$logic		= Logic_Mail::getInstance( $this->env );
		$orders		= array( 'paymentId' => 'ASC' );
		$indices	= array(
			'status'	=> Model_Shop_Payment_Stripe::STATUS_CREATED,
		 	'object'	=> '%"BANK_WIRE"%',
		);
		$openShopBankWirePayments	= array();
		foreach( $this->modelShopPayin->getAll( $indices, $orders ) as $payment )
			$openShopBankWirePayments[$payment->payInId]	= $payment;

		$succeededStripeBankWirePayments	= $this->modelStripePayin->getAll( array(
			'status'		=> Model_Stripe_Payin::STATUS_SUCCEEDED,							//  only succeeded payins
			'type'			=> Model_Stripe_Payin::TYPE_BANK_WIRE,							//  only bankwire payins
			'id'			=> array_keys( $openShopBankWirePayments ),							//  only for open shop payments
//			'modifiedAt'	=> '> '.( time() - 60 ),
		) );
		foreach( $succeededStripeBankWirePayments as $payment ){
			$shopPayment	= $openShopBankWirePayments[$payment->id];
			$payIn			= json_decode( $payment->data );
			$payIn			= $payIn->succeeded;
			if( $shopPayment->status == Model_Shop_Payment_Stripe::STATUS_CREATED ){
				$order	= $this->logicShop->getOrder( $shopPayment->orderId );
				$result	= $this->logicStripe->transferOrderAmountToClientSeller(
					$shopPayment->orderId,
					$payIn,
					TRUE
				);
				if( $result ){
					$this->logicStripe->updatePayment( $payIn );
					$this->logicShop->setOrderStatus( $shopPayment->orderId, Model_Shop_Order::STATUS_PAYED );
					$data		= array(
						'orderId'			=> $shopPayment->orderId,
						'paymentBackends'	=> $this->backends,
					);
					$logic->handleMail(
						new Mail_Shop_Customer_Payed( $this->env, $data ),
						$this->logicShop->getOrderCustomer( $order->orderId ),
						'de'
					);
					$logic->handleMail(
						new Mail_Shop_Manager_Payed( $this->env, $data ),
						(object) array( 'email' => $this->moduleConfig->get( 'mail.manager' ) ),
						'de'
					);
				}
			}
		}
	}
}
