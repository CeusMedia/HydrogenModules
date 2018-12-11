<?php
class Hook_Shop_Payment_Stripe extends CMF_Hydrogen_Hook{

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Hook context object
	 *	@param		object						$module		Module object
	 *	@param		public						$arguments	Map of hook arguments
	 *	@return		void
	 */
	static public function __onRegisterShopPaymentBackends( CMF_Hydrogen_Environment $env, $context, $module, $arguments = array() ){
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
				'klarna-2.png'							//  icon
//				'fa fa-fw fa-bank'						//  icon
			);
		}
		if( $methods->get( 'Giropay' ) ){
			$context->registerPaymentBackend(
				'Stripe',								//  backend class name
				'Stripe:Giropay',						//  payment method key
				$labels->giropay,						//  payment method label
				'stripe/perGiropay',					//  shop URL
	 			$methods->get( 'Giropay' ),				//  priority
				'giropay.png'							//  icon
//				'fa fa-fw fa-bank'						//  icon
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
	 *	@param		public						$arguments	Map of hook arguments
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
					$context->registerServicePanel( 'ShopPaymentStripe', $panelPayment, 2 );
				}
			}
		}
	}
}
