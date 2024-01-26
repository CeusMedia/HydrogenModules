<?php

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Shop_Payment_Paypal extends Hook
{
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
	public static function onRegisterShopPaymentBackends( Environment $env, object $context, object $module, array & $payload ): void
	{
		$methods	= $env->getConfig()->getAll( 'module.shop_payment_paypal.method.', TRUE );
		$words		= $env->getLanguage()->getWords( 'shop/payment/paypal' );
		$labels		= (object) $words['payment-methods'];
		/** @var Model_Shop_Payment_BackendRegister $register */
		$register	= $payload['register'] ?? new Model_Shop_Payment_BackendRegister( $env );
		if( $methods->get( 'Express' ) ){
			$register->add(
				'Paypal',									//  backend class name
				'PayPal:Express',							//  payment method key
				$labels->express,							//  payment method label
				'paypal/authorize',							//  shop URL
	 			$methods->get( 'Express' ),					//  priority
				'paypal-2.png'								//  icon
			);
		}
		$payload['register']	= $register;
/*		if( $methods->get( 'Express' ) ){
			$context->registerPaymentBackend(
				'Paypal',									//  backend class name
				'PayPal:Express',							//  payment method key
				$labels->express,							//  payment method label
				'paypal/authorize',							//  shop URL
	 			$methods->get( 'Express' ),					//  priority
				'paypal-2.png'								//  icon
			);
		}*/
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
	static public function onRenderServicePanels( Environment $env, object $context, object $module, array & $payload ): void
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
					$context->registerServicePanel( 'ShopPaymentPaypal', $panelPayment, 2 );
				}
			}
		}
	}
}
