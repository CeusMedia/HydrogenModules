<?php
class Hook_Shop_Payment_Bank/* extends CMF_Hydrogen_Hook*/{

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env			Environment instance
	 *	@param		object						$context		Hook context object
	 *	@param		object						$module			Module object
	 *	@param		public						$arguments		Map of hook arguments
	 *	@return		void
	 */
	static public function __onRegisterShopPaymentBackends( CMF_Hydrogen_Environment $env, $context, $module, $arguments = array() ){
		$methods	= $env->getConfig()->getAll( 'module.shop_payment_bank.method.', TRUE );
		$words		= $env->getLanguage()->getWords( 'shop/payment/bank' );
		$labels		= (object) $words['payment-methods'];
		if( $methods->get( 'Transfer' ) ){
			$context->registerPaymentBackend(
				'Bank',									//  backend class name
				'Bank:Transfer',						//  payment method key
				$labels->transfer,						//  payment method label
				'bank/perTransfer',						//  shop URL
				$methods->get( 'Transfer' ),			//  priority
				'bank-1.png'							//  icon
			);
		}
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env			Environment instance
	 *	@param		object						$context		Hook context object
	 *	@param		object						$module			Module object
	 *	@param		public						$arguments		Map of hook arguments
	 *	@return		void
	 */
	static public function __onRenderServicePanels( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
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
					$context->registerServicePanel( 'ShopPaymentBank', $panelPayment, 2 );
				}
			}
		}
	}
}
