<?php

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Shop_Payment_Stripe extends Hook
{
	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env		Environment instance
	 *	@param		object			$context	Hook context object
	 *	@param		object			$module		Module object
	 *	@param		array			$payload	Map of hook payload data
	 *	@return		void
	 */
	public static function onRegisterShopPaymentBackends( Environment $env, object $context, object $module, array & $payload ): void
	{
		$methods	= $env->getConfig()->getAll( 'module.shop_payment_stripe.method.', TRUE );
		$words		= $env->getLanguage()->getWords( 'shop/payment/stripe' );
		$labels		= (object) $words['payment-methods'];
		$descs		= (object) ( $words['payment-method-descriptions'] ?? [] );
		/** @var Model_Shop_Payment_BackendRegister $register */
		$register	= $payload['register'] ?? new Model_Shop_Payment_BackendRegister( $env );

		if( $methods->get( 'Card.active', FALSE ) ){
			$priority	= $methods->get( 'Card.priority', 0 );
			if( 0 !== $priority ){
				$method		= $methods->getAll( 'Card.', TRUE );

				$entity	= new Entity_Shop_Payment_Backend();
				$entity->backend		= 'Stripe';								//  backend class name
				$entity->key			= 'Stripe:Card';						//  payment method key
				$entity->path			= 'stripe/perCreditCard';				//  shop URL
				$entity->icon			= 'creditcard-1.png';					//  icon
//				$entity->icon			= 'fa fa-fw fa-credit-card';			//  icon
				$entity->priority		= $priority;							//  priority
				$entity->label			= $labels->card;						//  payment method label
				$entity->description	= $descs->card ?? '';
				$entity->feeExclusive	= $method->get( 'fee.exclusive' );
				$entity->feeFormula		= $method->get( 'fee.formula' );

				$register->addEntity( $entity );
			}
		}

		if( $methods->get( 'Sofort.active', FALSE ) ){
			$priority	= $methods->get( 'Sofort.priority', 0 );
			if( 0 !== $priority ){
				$method		= $methods->getAll( 'Sofort.', TRUE );

				$entity	= new Entity_Shop_Payment_Backend();
				$entity->backend		= 'Stripe';								//  backend class name
				$entity->key			= 'Stripe:Sofort';						//  payment method key
				$entity->path			= 'stripe/perSofort';					//  shop URL
				$entity->icon			= 'klarna-2.png';						//  icon
//				$entity->icon			= 'fa fa-fw fa-bank';					//  icon
				$entity->priority		= $priority;							//  priority
				$entity->label			= $labels->sofort;						//  payment method label
				$entity->description	= $descs->sofort ?? '';
				$entity->feeExclusive	= $method->get( 'fee.exclusive' );
				$entity->feeFormula		= $method->get( 'fee.formula' );
				$entity->countries		= ['AT', 'BE', 'DE', 'IT', 'NL', 'ES'];

				$register->addEntity( $entity );
			}
		}

		if( $methods->get( 'Giropay.active', FALSE ) ){
			$priority	= $methods->get( 'Giropay.priority', 0 );
			if( 0 !== $priority ){
				$method		= $methods->getAll( 'Giropay.', TRUE );

				$entity	= new Entity_Shop_Payment_Backend();
				$entity->backend		= 'Stripe';								//  backend class name
				$entity->key			= 'Stripe:Giropay';						//  payment method key
				$entity->path			= 'stripe/perGiropay';					//  shop URL
				$entity->icon			= 'giropay.png';						//  icon
//				$entity->icon			= 'fa fa-fw fa-bank';					//  icon
				$entity->priority		= $priority;							//  priority
				$entity->label			= $labels->giropay;						//  payment method label
				$entity->description	= $descs->transfer ?? '';
				$entity->feeExclusive	= $method->get( 'fee.exclusive' );
				$entity->feeFormula		= $method->get( 'fee.formula' );
				$entity->countries		= ['DE'];

				$register->addEntity( $entity );
			}
		}

		$payload['register']	= $register;
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env		Environment instance
	 *	@param		object			$context	Hook context object
	 *	@param		object			$module		Module object
	 *	@param		array			$payload	Map of hook arguments
	 *	@return		void
	 */
	public static function onRenderServicePanels( Environment $env, object $context, object $module, array & $payload ): void
	{
		if( empty( $payload['orderId'] ) || empty( $payload['paymentBackends']->getAll() ) )
			return;
		$model	= new Model_Shop_Order( $env );
		$order	= $model->get( $payload['orderId'] );
		foreach( $payload['paymentBackends']->getAll() as $backend ){
			if( $backend->key === $order->paymentMethod ){
				$className	= 'View_Helper_Shop_FinishPanel_'.$backend->backend;
				if( class_exists( $className ) ){
					$object	= ObjectFactory::createObject( $className, [$env] );
					$object->setOrderId( $payload['orderId'] );
					$object->setOutputFormat( $className::OUTPUT_FORMAT_HTML );
					$panelPayment	= $object->render();
					$context->registerServicePanel( 'ShopPaymentStripe', $panelPayment, 2 );
				}
			}
		}
	}
}
