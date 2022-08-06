<?php

use CeusMedia\HydrogenFramework\View;

class View_Helper_Shop_FinishPanel_CatalogClothing{

	protected $env;
	protected $orderId;
	protected $options;
	protected $address;

	public function __construct( $env ){
		$this->env			= $env;
		$this->logicShop	= new Logic_Shop( $this->env );
		$this->options		= $env->getConfig()->getAll( 'module.catalog_clothing.', TRUE );
	}

	public function __toString(){
		return $this->render();
	}

	public function render(){
		if( !$this->orderId )
			throw new RuntimeException( 'No order ID set' );
		$view	= new View( $this->env );

		$order			= $this->logicShop->getOrder( $this->orderId );
		$modelAddress	= new Model_Address( $this->env );
		$address		= $modelAddress->getByIndices( array(
			'relationType'	=> 'user',
			'relationId'	=> $order->userId,
			'type'			=> Model_Address::TYPE_DELIVERY,
		) );

		$helperAddress		= new View_Helper_Shop_AddressView( $this->env );
		$helperAddress->setAddress( $address );
		$data['address']	= $helperAddress->render();

		$helperCart			= new View_Helper_Shop_CartPositions( $this->env );
		$helperCart->setPositions( $this->logicShop->getOrderPositions( $this->orderId ) );
		$helperCart->setChangeable( FALSE );
		$cartDesktop	= UI_HTML_Tag::create( 'div', $helperCart->render(), array( 'class' => 'hidden-phone' ) );
		$helperCart->setOutput( View_Helper_Shop_CartPositions::OUTPUT_HTML_LIST );
		$cartPhone		= UI_HTML_Tag::create( 'div', $helperCart->render(), array( 'class' => 'visible-phone' ) );
		$data['cart']	= $cartDesktop.$cartPhone;

		return $view->loadContentFile( 'html/catalog/clothing/finished.html', $data );
	}

	public function setOrderId( $orderId ){
		$this->orderId	= $orderId;
		$order			= $this->logicShop->getOrder( $orderId );
		if( $this->env->getModules()->has( 'Resource_Address' ) ){
			$modelAddress	= new Model_Address( $this->env );
			$this->address	= $modelAddress->getByIndices( array(
				'relationType'	=> 'user',
				'relationId'	=> $order->userId,
				'type'			=> Model_Address::TYPE_DELIVERY,
			) );
		}
	}
}
