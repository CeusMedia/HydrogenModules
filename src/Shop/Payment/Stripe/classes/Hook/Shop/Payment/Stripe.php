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
		/** @var Model_Shop_Payment_BackendRegister $register */
		$register	= $payload['register'] ?? new Model_Shop_Payment_BackendRegister( $env );

		if( $methods->get( 'Card.priority', 0 ) ){
			$backend	= $register->add(
				'Stripe',								//  backend class name
				'Stripe:Card',							//  payment method key
				$labels->card,							//  payment method label
				'stripe/perCreditCard',					//  shop URL
	 			$methods->get( 'Card.priority' ),		//  priority
				'creditcard-1.png'						//  icon
//				'fa fa-fw fa-credit-card'				//  icon
			);
			$backend->costs	= $methods->get( 'Card.costs', 0 );
		}
		if( $methods->get( 'Sofort.priority', 0 ) ){
			$backend	= $register->add(
				'Stripe',								//  backend class name
				'Stripe:Sofort',						//  payment method key
				$labels->sofort,						//  payment method label
				'stripe/perSofort',						//  shop URL
				$methods->get( 'Sofort.priority' ),		//  priority
				'klarna-2.png'							//  icon
//					'fa fa-fw fa-bank'						//  icon
			);
			$backend->countries	= ['AT', 'BE', 'DE', 'IT', 'NL', 'ES'];
			$backend->costs	= $methods->get( 'Sofort.costs', 0 );
		}
		if( $methods->get( 'Giropay.priority', 0 ) ){
			$backend	= $register->add(
				'Stripe',								//  backend class name
				'Stripe:Giropay',						//  payment method key
				$labels->giropay,						//  payment method label
				'stripe/perGiropay',					//  shop URL
	 			$methods->get( 'Giropay.priority' ),		//  priority
				'giropay.png'							//  icon
//				'fa fa-fw fa-bank'						//  icon
			);
			$backend->countries	= ['DE'];
			$backend->costs	= $methods->get( 'Giropay.costs', 0 );
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
