<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_Shop_Payment_Stripe extends CMF_Hydrogen_Hook
{
	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Hook context object
	 *	@param		object						$module		Module object
	 *	@param		array						$payload	Map of hook payload data
	 *	@return		void
	 */
	public static function onRegisterShopPaymentBackends( Environment $env, $context, $module, $payload = [] ): void
	{
		$methods	= $env->getConfig()->getAll( 'module.shop_payment_stripe.method.', TRUE );
		$words		= $env->getLanguage()->getWords( 'shop/payment/stripe' );
		$labels		= (object) $words['payment-methods'];
		if( $methods->get( 'Card' ) ){
			$context->registerPaymentBackend(
				'Stripe',								//  backend class name
				'Stripe:Card',							//  payment method key
				$labels->card,							//  payment method label
				'stripe/perCreditCard',					//  shop URL
	 			$methods->get( 'Card' ),				//  priority
				'creditcard-1.png'						//  icon
//				'fa fa-fw fa-credit-card'				//  icon
			);
		}
		if( $methods->get( 'Sofort' ) ){
			$context->registerPaymentBackend(
				'Stripe',								//  backend class name
				'Stripe:Sofort',						//  payment method key
				$labels->sofort,						//  payment method label
				'stripe/perSofort',						//  shop URL
				$methods->get( 'Sofort' ),				//  priority
				'klarna-2.png',							//  icon
//					'fa fa-fw fa-bank'						//  icon
				array( 'AT', 'BE', 'DE', 'IT', 'NL', 'ES' )
			);
		}
		if( $methods->get( 'Giropay' ) ){
			$context->registerPaymentBackend(
				'Stripe',								//  backend class name
				'Stripe:Giropay',						//  payment method key
				$labels->giropay,						//  payment method label
				'stripe/perGiropay',					//  shop URL
	 			$methods->get( 'Giropay' ),				//  priority
				'giropay.png',							//  icon
//				'fa fa-fw fa-bank'						//  icon
				array( 'DE' )
			);
		}
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Hook context object
	 *	@param		object						$module		Module object
	 *	@param		public						$payload	Map of hook arguments
	 *	@return		void
	 */
	public static function onRenderServicePanels( Environment $env, $context, $module, $payload = [] )
	{
		if( empty( $payload['orderId'] ) || empty( $payload['paymentBackends'] ) )
			return;
		$model	= new Model_Shop_Order( $env );
		$order	= $model->get( $payload['orderId'] );
		foreach( $payload['paymentBackends'] as $backend ){
			if( $backend->key === $order->paymentMethod ){
				$className	= 'View_Helper_Shop_FinishPanel_'.$backend->backend;
				if( class_exists( $className ) ){
					$object	= Alg_Object_Factory::createObject( $className, array( $env ) );
					$object->setOrderId( $payload['orderId'] );
					$object->setOutputFormat( $className::OUTPUT_FORMAT_HTML );
					$panelPayment	= $object->render();
					$context->registerServicePanel( 'ShopPaymentStripe', $panelPayment, 2 );
				}
			}
		}
	}
}
