<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Shop_Payment_Bank extends Hook
{
	/**
	 *	Hook to register two bank payment backends.
	 *	@access		public
	 *	@return		void
	 */
	public function onRegisterShopPaymentBackends(): void
	{
		$payload	= $this->getPayload() ?? [];

		$methods	= $this->env->getConfig()->getAll( 'module.shop_payment_bank.method.', TRUE );
		$words		= $this->env->getLanguage()->getWords( 'shop/payment/bank' );
		$labels		= (object) $words['payment-methods'];
		$descs		= (object) ( $words['payment-method-descriptions'] ?? [] );

		/** @var Model_Shop_Payment_BackendRegister $register */
		$register	= $payload['register'] ?? new Model_Shop_Payment_BackendRegister( $this->env );
		if( $methods->get( 'Transfer.active', FALSE ) ){
			$priority	= $methods->get( 'Transfer.priority', 0 );
			$method		= $methods->getAll( 'Transfer.', TRUE );
			if( 0 !== $priority ){
				$register->addEntity( new Entity_Shop_Payment_Backend( [
					'backend'		=> 'Bank',								//  backend class name
					'key'			=> 'Bank:Transfer',						//  payment method key
					'path'			=> 'bank/perTransfer',					//  shop URL
					'icon'			=> 'bank-transfer.png',					//  icon
					'priority'		=> $priority,							//  priority
					'title'			=> $labels->transfer,					//  payment method label
					'description'	=> $descs->transfer ?? '',
					'feeExclusive'	=> $method->get( 'fee.exclusive' ),
					'feeFormula'	=> $method->get( 'fee.formula' ),
				] ) );
			}
		}

		if( $methods->get( 'Bill.active', FALSE ) ){
			$priority	= $methods->get( 'Bill.priority', 0 );
			$method		= $methods->getAll( 'Bill.', TRUE );
			if( 0 !== $priority ){
				$register->addEntity( new Entity_Shop_Payment_Backend( [
					'backend'		=> 'Bank',								//  backend class name
					'key'			=> 'Bank:Bill',							//  payment method key
					'path'			=> 'bank/perBill',						//  shop URL
					'icon'			=> 'bank-bill.png',						//  icon
					'priority'		=> $priority,							//  priority
					'title'			=> $labels->bill,						//  payment method label
					'description'	=> $descs->transfer ?? '',
					'feeExclusive'	=> $method->get( 'fee.exclusive' ),
					'feeFormula'	=> $method->get( 'fee.formula' ),
				] ) );
			}
		}
		$payload['register']	= $register;
		$this->setPayload( $payload );
	}

	/**
	 *	Hook to register panel for final shop screen.
	 *	@access		public
	 *	@return		void
	 *	@throws		RuntimeException		if payload is missing orderId
	 *	@throws		RuntimeException		if payload is missing paymentBackends
	 *	@throws		RuntimeException		if paymentBackends is not of Model_Shop_Payment_BackendRegister
	 *	@throws		RuntimeException		if orderId is invalid
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onRenderServicePanels(): void
	{
		$payload	= new Dictionary( $this->getPayload() ?? [] );
		if( !$payload->has( 'orderId' ) )
			throw new RuntimeException( 'No order ID set in payload' );
		if( !$payload->has( 'paymentBackends' ) )
			throw new RuntimeException( 'No payload backends in payload' );

		$backendRegistry	= $payload->get( 'paymentBackends' );
		if( !$backendRegistry instanceof Model_Shop_Payment_BackendRegister )
			throw new RuntimeException( 'Payload must have paymentBackends by Model_Shop_Payment_BackendRegister' );

		$paymentBackends	= $backendRegistry->getAll();
		if( [] === $paymentBackends )
			return;

		$model		= new Model_Shop_Order( $this->env );
		$orderId	= $payload->get( 'orderId' );
		$order		= $model->get( $orderId );
		if( NULL === $order )
			throw new RuntimeException( 'Invalid order ID set' );

		foreach( $backendRegistry->getAll() as $backend ){
			if( $backend->key === $order->paymentMethod ){
				$className	= 'View_Helper_Shop_FinishPanel_'.$backend->backend;
				if( class_exists( $className ) ){
					$object	= ObjectFactory::createObject( $className, [$this->env] );
					$object->setOrderId( $orderId );
					$object->setOutputFormat( $className::OUTPUT_FORMAT_HTML );
					$panelPayment	= $object->render();
					$this->context->registerServicePanel( 'ShopPaymentBank', $panelPayment, 2 );
				}
			}
		}
	}
}
