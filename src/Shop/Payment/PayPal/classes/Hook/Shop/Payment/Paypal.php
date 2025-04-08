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
		$descs		= (object) ( $words['payment-method-descriptions'] ?? [] );
		/** @var Model_Shop_Payment_BackendRegister $register */
		$register	= $payload['register'] ?? new Model_Shop_Payment_BackendRegister( $env );

		if( $methods->get( 'Express.active', FALSE ) ){
			$priority	= $methods->get( 'Express.priority', 0 );
			if( 0 !== $priority ){
				$method		= $methods->getAll( 'Express.', TRUE );

				$entity	= new Entity_Shop_Payment_Backend();
				$entity->backend		= 'Paypal';								//  backend class name
				$entity->key			= 'PayPal:Express';						//  payment method key
				$entity->path			= 'paypal/authorize';					//  shop URL
				$entity->icon			= 'paypal-2.png';						//  icon
				$entity->priority		= $priority;							//  priority
				$entity->label			= $labels->express;						//  payment method label
				$entity->description	= $descs->transfer ?? '';
				$entity->feeExclusive	= $method->get( 'fee.exclusive' );
				$entity->feeFormula		= $method->get( 'fee.formula' );

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
