<?php

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Shop_Payment_Bank extends Hook
{
	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env			Environment instance
	 *	@param		object			$context		Hook context object
	 *	@param		object			$module			Module object
	 *	@param		array			$payload		Map of hook arguments
	 *	@return		void
	 */
	public static function onRegisterShopPaymentBackends( Environment $env, object $context, object $module, array & $payload )
	{
		$methods	= $env->getConfig()->getAll( 'module.shop_payment_bank.method.', TRUE );
		$words		= $env->getLanguage()->getWords( 'shop/payment/bank' );
		$labels		= (object) $words['payment-methods'];
		$descs		= (object) ( $words['payment-method-descriptions'] ?? [] );
		/** @var Model_Shop_Payment_BackendRegister $register */
		$register	= $payload['register'] ?? new Model_Shop_Payment_BackendRegister( $env );

		if( $methods->get( 'Transfer.active', FALSE ) ){
			$priority	= $methods->get( 'Transfer.priority', 0 );
			if( 0 !== $priority ){
				$method		= $methods->getAll( 'Transfer.', TRUE );
				$register->add( [
					'backend'		=> 'Bank',									//  backend class name
					'key'			=> 'Bank:Transfer',							//  payment method key
					'path'			=> 'bank/perTransfer',						//  shop URL
					'icon'			=> 'bank-1.png',							//  icon
//					'icon'			=> 'fa fa-fw fa-bank',						//  icon
					'priority'		=> $priority,								//  priority
					'label'			=> $labels->transfer,						//  payment method label
					'description'	=> $descs->transfer ?? '',
					'feeExclusive'	=> $method->get( 'fee.exclusive' ),
					'feeFormula'	=> $method->get( 'fee.formula' ),
				] );
			}
		}

		if( $methods->get( 'Bill.active', FALSE ) ){
			$priority	= $methods->get( 'Bill.priority', 0 );
			if( 0 !== $priority ){
				$method		= $methods->getAll( 'Bill.', TRUE );
				$register->add( [
					'backend'		=> 'Bank',									//  backend class name
					'key'			=> 'Bank:Bill',								//  payment method key
					'path'			=> 'bank/perBill',							//  shop URL
					'icon'			=> 'bank-bill.png',							//  icon
//					'icon'			=> 'fa fa-fw fa-bank',						//  icon
					'priority'		=> $priority,								//  priority
					'label'			=> $labels->bill,							//  payment method label
					'description'	=> $descs->transfer ?? '',
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
	 *	@param		array			$payload		Map of hook arguments
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
					$context->registerServicePanel( 'ShopPaymentBank', $panelPayment, 2 );
				}
			}
		}
	}
}
