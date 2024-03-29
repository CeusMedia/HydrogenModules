<?php

use CeusMedia\Common\ADT\Collection\Dictionary;

class Job_Shop_Payment_Mangopay extends Job_Abstract
{
	protected Logic_Shop_Payment_Mangopay $logicMangopay;
	protected Logic_Shop $logicShop;
	protected Model_Mangopay_Event $modelEvent;
	protected Model_Mangopay_Payin $modelMangopayPayin;
	protected Model_Shop_Payment_Mangopay $modelShopPayin;
	protected Dictionary $moduleConfig;
	protected array $backends				= [];

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function handle()
	{
		$this->handleFailedBankWirePayIns();
		$this->handleSucceededBankWirePayIns();
	}

	/**
	 *	Register a payment backend.
	 *	@access		public
	 *	@param		string			$backend		...
	 *	@param		string			$key			...
	 *	@param		string			$title			...
	 *	@param		string			$path			...
	 *	@param		integer			$priority		...
	 *	@param		string|NULL		$icon			...
	 *	@param		array			$countries		...
	 *	@return		void
	 */
	public function registerPaymentBackend( string $backend, string $key, string $title, string $path, int $priority = 5, string $icon = NULL, array $countries = [] ): void
	{
		$this->backends[]	= (object) [
			'backend'	=> $backend,
			'key'		=> $key,
			'title'		=> $title,
			'path'		=> $path,
			'priority'	=> $priority,
			'icon'		=> $icon,
			'countries'	=> $countries,
		];
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logicMangopay		= new Logic_Shop_Payment_Mangopay( $this->env );
		$this->logicShop			= new Logic_Shop( $this->env );
		$this->modelEvent			= new Model_Mangopay_Event( $this->env );
		$this->modelMangopayPayin	= new Model_Mangopay_Payin( $this->env );
		$this->modelShopPayin		= new Model_Shop_Payment_Mangopay( $this->env );
		$this->moduleConfig			= $this->env->getConfig()->getAll( 'module.shop.', TRUE );

		$captain	= $this->env->getCaptain();
		$payload	= [];
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, $payload );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function handleFailedBankWirePayIns()
	{
		$logic		= Logic_Mail::getInstance( $this->env );
		$orders		= ['paymentId' => 'ASC'];
		$indices	= [
			'status'	=> Model_Shop_Payment_Mangopay::STATUS_CREATED,
		 	'object'	=> '%"BANK_WIRE"%',
		];
		$openShopBankWirePayments	= [];
		foreach( $this->modelShopPayin->getAll( $indices, $orders ) as $payment )
			$openShopBankWirePayments[$payment->payInId]	= $payment;

		$failedMangoPayBankWirePayments	= $this->modelMangopayPayin->getAll( array(
			'status'		=> Model_Mangopay_Payin::STATUS_FAILED,								//  only failed payins
			'type'			=> Model_Mangopay_Payin::TYPE_BANK_WIRE,							//  only bankwire payins
			'id'			=> array_keys( $openShopBankWirePayments ),							//  only for open shop payments
//			'modifiedAt'	=> '> '.( time() - 60 ),
		) );
		foreach( $failedMangoPayBankWirePayments as $payment ){
			$shopPayment	= $openShopBankWirePayments[$payment->id];
			$payIn			= json_decode( $payment->data );
			$payIn			= $payIn->failed;
			if( $shopPayment->status == Model_Shop_Payment_Mangopay::STATUS_CREATED ){
				$order	= $this->logicShop->getOrder( $shopPayment->orderId );
				$this->logicMangopay->updatePayment( $payIn );
				$this->logicShop->setOrderStatus( $shopPayment->orderId, Model_Shop_Order::STATUS_NOT_PAYED );

				$data		= [
					'orderId'			=> $shopPayment->orderId,
					'paymentBackends'	=> $this->backends,
				];
				$logic->handleMail(
					new Mail_Shop_Customer_NotPayed( $this->env, $data ),
					$this->logicShop->getOrderCustomer( $order->orderId ),
					'de'
				);
				$logic->handleMail(
					new Mail_Shop_Manager_NotPayed( $this->env, $data ),
					(object) ['email' => $this->moduleConfig->get( 'mail.manager' )],
					'de'
				);
			}
		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function handleSucceededBankWirePayIns()
	{
		$logic		= Logic_Mail::getInstance( $this->env );
		$orders		= ['paymentId' => 'ASC'];
		$indices	= [
			'status'	=> Model_Shop_Payment_Mangopay::STATUS_CREATED,
		 	'object'	=> '%"BANK_WIRE"%',
		];
		$openShopBankWirePayments	= [];
		foreach( $this->modelShopPayin->getAll( $indices, $orders ) as $payment )
			$openShopBankWirePayments[$payment->payInId]	= $payment;

		$succeededMangoPayBankWirePayments	= $this->modelMangopayPayin->getAll( array(
			'status'		=> Model_Mangopay_Payin::STATUS_SUCCEEDED,							//  only succeeded payins
			'type'			=> Model_Mangopay_Payin::TYPE_BANK_WIRE,							//  only bankwire payins
			'id'			=> array_keys( $openShopBankWirePayments ),							//  only for open shop payments
//			'modifiedAt'	=> '> '.( time() - 60 ),
		) );
		foreach( $succeededMangoPayBankWirePayments as $payment ){
			$shopPayment	= $openShopBankWirePayments[$payment->id];
			$payIn			= json_decode( $payment->data );
			$payIn			= $payIn->succeeded;
			if( $shopPayment->status == Model_Shop_Payment_Mangopay::STATUS_CREATED ){
				$order	= $this->logicShop->getOrder( $shopPayment->orderId );
				$result	= $this->logicMangopay->transferOrderAmountToClientSeller(
					$shopPayment->orderId,
					$payIn,
					TRUE
				);
				if( $result ){
					$this->logicMangopay->updatePayment( $payIn );
					$this->logicShop->setOrderStatus( $shopPayment->orderId, Model_Shop_Order::STATUS_PAYED );
					$data		= [
						'orderId'			=> $shopPayment->orderId,
						'paymentBackends'	=> $this->backends,
					];
					$logic->handleMail(
						new Mail_Shop_Customer_Payed( $this->env, $data ),
						$this->logicShop->getOrderCustomer( $order->orderId ),
						'de'
					);
					$logic->handleMail(
						new Mail_Shop_Manager_Payed( $this->env, $data ),
						(object) ['email' => $this->moduleConfig->get( 'mail.manager' )],
						'de'
					);
				}
			}
		}
	}
}
