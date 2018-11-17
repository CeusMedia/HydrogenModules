<?php
class View_Shop extends CMF_Hydrogen_View{

/*	public function address(){
}*/

	public function cart(){
	}

	public function checkout(){
	}

	public function conditions(){
	}

/*	public function customer(){
}*/

	public function payment(){
	}

	public function rules(){
		return $this->loadContent( 'shop', 'rules' );
	}

	public function service(){
		$panelPayment	= '';
		if( ( $orderId = $this->getData( 'orderId' ) ) ){
			$order		= $this->getData( 'order' );
			$backends	= $this->getData( 'paymentBackends' );
			foreach( $backends as $backend ){
				if( $backend->key === $order->paymentMethod ){
					$className	= 'View_Helper_Shop_Payment_FinishPanel_'.$backend->backend;
					if( class_exists( $className ) ){
						$object	= Alg_Object_Factory::createObject( $className, array( $this->env ) );
						$object->setOrderId( $orderId );
						$object->setOutputFormat( $className::OUTPUT_FORMAT_HTML );
						$panelPayment	= $object->render();
					}
				}
			}
		}
		$this->addData( 'panelPayment', $panelPayment );
	}

	public function register(){
	}
}
