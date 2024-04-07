<?php

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\View;

class View_Shop extends View
{
/*	public function address()
	{
}*/

	public function cart()
	{
	}

	public function checkout()
	{
	}

	public function conditions()
	{
	}

/*	public function customer()
	{
}*/

	public function payment()
	{
	}

	public function rules(): string
	{
		return $this->loadContent( 'shop', 'rules' );
	}

	public function service(): void
	{
		$panelPayment	= '';
		if( ( $orderId = $this->getData( 'orderId' ) ) ){
			$order		= $this->getData( 'order' );
			$backends	= $this->getData( 'paymentBackends' );
			foreach( $backends as $backend ){
				if( $backend->key === $order->paymentMethod ){
					$className	= 'View_Helper_Shop_Payment_FinishPanel_'.$backend->backend;
					if( class_exists( $className ) ){
						$object	= ObjectFactory::createObject( $className, [$this->env] );
						$object->setOrderId( $orderId );
						$object->setOutputFormat( $className::OUTPUT_FORMAT_HTML );
						$panelPayment	= $object->render();
					}
				}
			}
		}
		$this->addData( 'panelPayment', $panelPayment );
	}

	public function register()
	{
	}
}
