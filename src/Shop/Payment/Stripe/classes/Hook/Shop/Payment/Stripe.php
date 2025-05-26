<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Shop_Payment_Stripe extends Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 */
	public function onRegisterShopPaymentBackends(): void
	{
		$methods	= $this->env->getConfig()->getAll( 'module.shop_payment_stripe.method.', TRUE );
		$words		= $this->env->getLanguage()->getWords( 'shop/payment/stripe' );
		$labels		= (object) $words['payment-methods'];
		$descs		= (object) ( $words['payment-method-descriptions'] ?? [] );
		/** @var Model_Shop_Payment_BackendRegister $register */
		$register	= $payload['register'] ?? new Model_Shop_Payment_BackendRegister( $this->env );

		if( $methods->get( 'Card.active', FALSE ) ){
			$priority	= $methods->get( 'Card.priority', 0 );
			if( 0 !== $priority ){
				$method		= $methods->getAll( 'Card.', TRUE );
				$register->addEntity( new Entity_Shop_Payment_Backend( [
					'backend'		=> 'Stripe',								//  backend class name
					'key'			=> 'Stripe:Card',							//  payment method key
					'path'			=> 'stripe/perCreditCard',					//  shop URL
					'icon'			=> 'creditcard-1.png',						//  icon
					'priority'		=> $priority,								//  priority
					'title'			=> $labels->card,							//  payment method label
					'description'	=> $descs->card ?? '',
					'feeExclusive'	=> $method->get( 'fee.exclusive' ),
					'feeFormula'	=> $method->get( 'fee.formula' ),
				] ) );
			}
		}

		if( $methods->get( 'Sofort.active', FALSE ) ){
			$priority	= $methods->get( 'Sofort.priority', 0 );
			if( 0 !== $priority ){
				$method		= $methods->getAll( 'Sofort.', TRUE );
				$register->addEntity( new Entity_Shop_Payment_Backend( [
					'backend'		=> 'Stripe',								//  backend class name
					'key'			=> 'Stripe:Sofort',							//  payment method key
					'path'			=> 'stripe/perSofort',						//  shop URL
					'icon'			=> 'klarna-2.png',							//  icon
					'priority'		=> $priority,								//  priority
					'title'			=> $labels->sofort,							//  payment method label
					'description'	=> $descs->sofort ?? '',
					'feeExclusive'	=> $method->get( 'fee.exclusive' ),
					'feeFormula'	=> $method->get( 'fee.formula' ),
					'countries'		=> ['AT', 'BE', 'DE', 'IT', 'NL', 'ES'],
				] ) );
			}
		}

		if( $methods->get( 'Giropay.active', FALSE ) ){
			$priority	= $methods->get( 'Giropay.priority', 0 );
			if( 0 !== $priority ){
				$method		= $methods->getAll( 'Giropay.', TRUE );
				$register->addEntity( new Entity_Shop_Payment_Backend( [
					'backend'		=> 'Stripe',								//  backend class name
					'key'			=> 'Stripe:Giropay',						//  payment method key
					'path'			=> 'stripe/perGiropay',						//  shop URL
					'icon'			=> 'giropay.png',							//  icon
					'priority'		=> $priority,								//  priority
					'title'			=> $labels->giropay,						//  payment method label
					'description'	=> $descs->transfer ?? '',
					'feeExclusive'	=> $method->get( 'fee.exclusive' ),
					'feeFormula'	=> $method->get( 'fee.formula' ),
					'countries'		=> ['DE'],
				] ) );
			}
		}
		$payload['register']	= $register;
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
		foreach( $paymentBackends->getAll() as $backend ){
			if( $backend->key === $order->paymentMethod ){
				$className	= 'View_Helper_Shop_FinishPanel_'.$backend->backend;
				if( class_exists( $className ) ){
					$object	= ObjectFactory::createObject( $className, [$this->env] );
					$object->setOrderId( $orderId );
					$object->setOutputFormat( $className::OUTPUT_FORMAT_HTML );
					$panelPayment	= $object->render();
					$this->context->registerServicePanel( 'ShopPaymentStripe', $panelPayment, 2 );
				}
			}
		}
	}
}
