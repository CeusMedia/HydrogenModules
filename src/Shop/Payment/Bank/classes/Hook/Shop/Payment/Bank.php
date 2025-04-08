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

				$entity	= new Entity_Shop_Payment_Backend();
				$entity->backend		= 'Bank';								//  backend class name
				$entity->key			= 'Bank:Transfer';						//  payment method key
				$entity->path			= 'bank/perTransfer';					//  shop URL
				$entity->icon			= 'bank-1.png';							//  icon
//				$entity->icon			= 'fa fa-fw fa-bank';					//  icon
				$entity->priority		= $priority;							//  priority
				$entity->label			= $labels->transfer;					//  payment method label
				$entity->description	= $descs->transfer ?? '';
				$entity->feeExclusive	= $method->get( 'fee.exclusive' );
				$entity->feeFormula		= $method->get( 'fee.formula' );
				$register->addEntity( $entity );
			}
		}

		if( $methods->get( 'Bill.active', FALSE ) ){
			$priority	= $methods->get( 'Bill.priority', 0 );
			if( 0 !== $priority ){
				$method		= $methods->getAll( 'Bill.', TRUE );

				$entity	= new Entity_Shop_Payment_Backend();
				$entity->backend		= 'Bank';								//  backend class name
				$entity->key			= 'Bank:Bill';							//  payment method key
				$entity->path			= 'bank/perBill';						//  shop URL
				$entity->icon			= 'bank-bill.png';						//  icon
//				$entity->icon			= 'fa fa-fw fa-bank';					//  icon
				$entity->priority		= $priority;							//  priority
				$entity->label			= $labels->bill;						//  payment method label
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
