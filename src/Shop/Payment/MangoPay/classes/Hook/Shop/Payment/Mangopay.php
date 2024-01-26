<?php

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
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
	 *	@param		array			$payload		Data object of hook arguments
	 *	@return		void
	 */
	public static function onRegisterShopPaymentBackends( Environment $env, object $context, object $module, array & $payload ): void
	{
		$methods	= $env->getConfig()->getAll( 'module.shop_payment_mangopay.method.', TRUE );
		/** @var Model_Shop_Payment_BackendRegister $register */
		$register	= $payload['register'] ?? new Model_Shop_Payment_BackendRegister( $env );
		if( $methods->get( 'CreditCardWeb.priority', 0 ) ){
			$backend	= $register->add(
				'Mangopay',													//  backend class name
				'MangopayCCW',													//  payment method key
				'Kreditkarte',													//  payment method label
				'mangopay/perCreditCard',										//  shop URL
	 			$methods->get( 'CreditCardWeb.priority' ),						//  priority
				'fa fa-fw fa-credit-card'										//  icon
			);
			$backend->costs	= $methods->get( 'CreditCardWeb.costs', 0 );
		}
		if( $methods->get( 'BankWire.priority', 0 ) ){
			$backend	= $register->add(
				'Mangopay',													//  backend class name
				'MangopayBW',													//  payment method key
				'Vorkasse',													//  payment method label
				'mangopay/perBankWire',										//  shop URL
	 			$methods->get( 'BankWire.priority' ),							//  priority
				'fa fa-fw fa-pencil-square-o'									//  icon
			);
			$backend->costs	= $methods->get( 'BankWire.costs', 0 );
		}
		if( $methods->get( 'BankWireWeb.priority', 0 ) ){
			$backend	= $register->add(
				'Mangopay',													//  backend class name
				'MangopayBWW',													//  payment method key
				'Sofortüberweisung',											//  payment method label
				'mangopay/perDirectDebit',										//  shop URL
	 			$methods->get( 'BankWireWeb.priority' ),						//  priority
				'fa fa-fw fa-bank'												//  icon
			);
			$backend->costs	= $methods->get( 'BankWireWeb.costs', 0 );
		}
		$payload['register']	= $register;
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env			Environment instance
	 *	@param		object			$context		Hook context object
	 *	@param		object			$module			Module object
	 *	@param		array			$payload		Data object of hook arguments
	 *	@return		void
	 */
	public static function onRenderServicePanels( Environment $env, object $context, object $module, array & $payload ): void
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
			$object	= ObjectFactory::createObject( $className, [$env] );
			$object->setOrderId( $data->orderId );
			$object->setOutputFormat( $className::OUTPUT_FORMAT_HTML );
			$panelPayment	= $object->render();
			$context->registerServicePanel( 'ShopPaymentMangoPay', $panelPayment, 5 );
		}
	}
}
