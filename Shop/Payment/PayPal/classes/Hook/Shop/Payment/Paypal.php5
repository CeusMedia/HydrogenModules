<?php
class Hook_Shop_Payment_Paypal/* extends CMF_Hydrogen_Hook*/{

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Environment instance
	 *	@param		object								$context	Hook context object
	 *	@param		object								$module		Module object
	 *	@param		public								$arguments	Map of hook arguments
	 *	@return		void
	 */
	static public function __onRegisterShopPaymentBackends( $env, $context, $module, $arguments = array() ){
		$methods	= $env->getConfig()->getAll( 'module.shop_payment_paypal.method.', TRUE );
		$words		= $env->getLanguage()->getWords( 'shop/payment/paypal' );
		$labels		= (object) $words['payment-methods'];
		if( $methods->get( 'Express' ) ){
			$context->registerPaymentBackend(
				'Paypal',									//  backend class name
				'PayPal:Express',							//  payment method key
				$labels->express,							//  payment method label
				'paypal/authorize',							//  shop URL
	 			$methods->get( 'Express' ),					//  priority
				'paypal-2.png'								//  icon
			);
		}
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Environment instance
	 *	@param		object								$context	Hook context object
	 *	@param		object								$module		Module object
	 *	@param		public								$arguments	Map of hook arguments
	 *	@return		void
	 */
	static public function __onRenderServicePanels( $env, $context, $module, $data = array() ){
		if( empty( $data['orderId'] ) || empty( $data['paymentBackends'] ) )
			return;
		$model	= new Model_Shop_Order( $env );
		$order	= $model->get( $data['orderId'] );
		foreach( $data['paymentBackends'] as $backend ){
			if( $backend->key === $order->paymentMethod ){
				$className	= 'View_Helper_Shop_FinishPanel_'.$backend->backend;
				if( class_exists( $className ) ){
					$object	= Alg_Object_Factory::createObject( $className, array( $env ) );
					$object->setOrderId( $data['orderId'] );
					$object->setOutputFormat( $className::OUTPUT_FORMAT_HTML );
					$panelPayment	= $object->render();
					$context->registerServicePanel( 'ShopPaymentPaypal', $panelPayment, 2 );
				}
			}
		}
	}
}
