<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Shop_Payment_Mangopay extends Hook
{
	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env			Environment instance
	 *	@param		object			$context		Hook context object
	 *	@param		object			$module			Module object
	 *	@param		object			$payload		Data object of hook arguments
	 *	@return		void
	 */
	public static function onRegisterShopPaymentBackends( Environment $env, $context, $module, $payload )
	{
		$methods	= $env->getConfig()->getAll( 'module.shop_payment_mangopay.method.', TRUE );
		if( $methods->get( 'CreditCardWeb' ) ){
			$context->registerPaymentBackend(
				'Mangopay',														//  backend class name
				'MangopayCCW',													//  payment method key
				'Kreditkarte',													//  payment method label
				'mangopay/perCreditCard',										//  shop URL
	 			$methods->get( 'CreditCardWeb' ),								//  priority
				'fa fa-fw fa-credit-card'										//  icon
			);
		}
		if( $methods->get( 'BankWire' ) ){
			$context->registerPaymentBackend(
				'Mangopay',														//  backend class name
				'MangopayBW',													//  payment method key
				'Vorkasse',														//  payment method label
				'mangopay/perBankWire',											//  shop URL
	 			$methods->get( 'BankWire' ),									//  priority
				'fa fa-fw fa-pencil-square-o'									//  icon
			);
		}
		if( $methods->get( 'BankWireWeb' ) ){
			$context->registerPaymentBackend(
				'Mangopay',														//  backend class name
				'MangopayBWW',													//  payment method key
				'Sofortüberweisung',											//  payment method label
				'mangopay/perDirectDebit',										//  shop URL
	 			$methods->get( 'BankWireWeb' ),									//  priority
				'fa fa-fw fa-bank'												//  icon
			);
		}
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env			Environment instance
	 *	@param		object			$context		Hook context object
	 *	@param		object			$module			Module object
	 *	@param		object			$payload		Data object of hook arguments
	 *	@return		void
	 */
	public static function onRenderServicePanels( Environment $env, $context, $module, $payload )
	{
		$data	= (object) $payload;
		if( empty( $data->orderId ) || empty( $data->paymentBackends ) )
			return;
		$model	= new Model_Shop_Order( $env );
		$order	= $model->get( $data->orderId );
		foreach( $data->paymentBackends as $backend ){
			if( $backend->key !== $order->paymentMethod )
				continue;
			$className	= 'View_Helper_Shop_FinishPanel_'.$backend->backend;
			if( !class_exists( $className ) )
				continue;
			$object	= Alg_Object_Factory::createObject( $className, [$env] );
			$object->setOrderId( $data->orderId );
			$object->setOutputFormat( $className::OUTPUT_FORMAT_HTML );
			$panelPayment	= $object->render();
			$context->registerServicePanel( 'ShopPaymentMangoPay', $panelPayment, 5 );
		}
	}
}
