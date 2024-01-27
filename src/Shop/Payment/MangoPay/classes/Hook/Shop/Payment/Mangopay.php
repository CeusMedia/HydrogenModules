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


		if( $methods->get( 'CreditCardWeb.active', FALSE ) ){
			$priority	= $methods->get( 'CreditCardWeb.priority', 0 );
			if( 0 !== $priority ){
				$method		= $methods->getAll( 'CreditCardWeb.', TRUE );
				$register->add( [
					'backend'		=> 'Mangopay',								//  backend class name
					'key'			=> 'MangopayCCW',							//  payment method key
					'path'			=> 'mangopay/perCreditCard',				//  shop URL
					'icon'			=> 'fa fa-fw fa-credit-card',				//  icon
					'priority'		=> $priority,								//  priority
					'label'			=> 'Kreditkarte',							//  payment method label
					'feeExclusive'	=> $method->get( 'fee.exclusive' ),
					'feeFormula'	=> $method->get( 'fee.formula' ),
				] );
			}
		}

		if( $methods->get( 'BankWire.active', FALSE ) ){
			$priority	= $methods->get( 'BankWire.priority', 0 );
			if( 0 !== $priority ){
				$method		= $methods->getAll( 'BankWire.', TRUE );
				$register->add( [
					'backend'		=> 'Mangopay',								//  backend class name
					'key'			=> 'MangopayBW',							//  payment method key
					'path'			=> 'mangopay/perBankWire',					//  shop URL
					'icon'			=> 'fa fa-fw fa-pencil-square-o',			//  icon
					'priority'		=> $priority,								//  priority
					'label'			=> 'Vorkasse',								//  payment method label
					'feeExclusive'	=> $method->get( 'fee.exclusive' ),
					'feeFormula'	=> $method->get( 'fee.formula' ),
				] );
			}
		}

		if( $methods->get( 'BankWireWeb.active', FALSE ) ){
			$priority	= $methods->get( 'BankWireWeb.priority', 0 );
			if( 0 !== $priority ){
				$method		= $methods->getAll( 'BankWireWeb.', TRUE );
				$register->add( [
					'backend'		=> 'Mangopay',								//  backend class name
					'key'			=> 'MangopayBWW',							//  payment method key
					'path'			=> 'mangopay/perDirectDebit',				//  shop URL
					'icon'			=> 'fa fa-fw fa-bank',						//  icon
					'priority'		=> $priority,								//  priority
					'label'			=> 'Sofortüberweisung',						//  payment method label
					'feeExclusive'	=> $method->get( 'fee.exclusive' ),
					'feeFormula'	=> $method->get( 'fee.formula' ),
				] );
			}
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
