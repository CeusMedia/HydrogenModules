<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

class View_Helper_Shop_FinishPanel_CatalogClothing
{
	protected WebEnvironment $env;
	protected Logic_Shop $logicShop;
	protected Dictionary $options;
	protected ?string $orderId			= NULL;
	protected ?object $address			= NULL;

	public function __construct( WebEnvironment $env )
	{
		$this->env			= $env;
		$this->logicShop	= new Logic_Shop( $this->env );
		$this->options		= $env->getConfig()->getAll( 'module.catalog_clothing.', TRUE );
	}

	public function __toString(): string
	{
		return $this->render();
	}

	public function render(): string
	{
		if( !$this->orderId )
			throw new RuntimeException( 'No order ID set' );
		$view	= new View( $this->env );

		$order			= $this->logicShop->getOrder( $this->orderId );
		$modelAddress	= new Model_Address( $this->env );
		$address		= $modelAddress->getByIndices( [
			'relationType'	=> 'user',
			'relationId'	=> $order->userId,
			'type'			=> Model_Address::TYPE_DELIVERY,
		] );

		$helperAddress		= new View_Helper_Shop_AddressView( $this->env );
		$helperAddress->setAddress( $address );
		$data['address']	= $helperAddress->render();

		$helperCart			= new View_Helper_Shop_CartPositions( $this->env );
		$helperCart->setPositions( $this->logicShop->getOrderPositions( $this->orderId ) );
		$helperCart->setChangeable( FALSE );
		$cartDesktop	= HtmlTag::create( 'div', $helperCart->render(), ['class' => 'hidden-phone'] );
		$helperCart->setOutput( View_Helper_Shop_CartPositions::OUTPUT_HTML_LIST );
		$cartPhone		= HtmlTag::create( 'div', $helperCart->render(), ['class' => 'visible-phone'] );
		$data['cart']	= $cartDesktop.$cartPhone;

		return $view->loadContentFile( 'html/catalog/clothing/finished.html', $data );
	}

	public function setOrderId( string $orderId ): self
	{
		$this->orderId	= $orderId;
		$order			= $this->logicShop->getOrder( $orderId );
		if( $this->env->getModules()->has( 'Resource_Address' ) ){
			$modelAddress	= new Model_Address( $this->env );
			$this->address	= $modelAddress->getByIndices( [
				'relationType'	=> 'user',
				'relationId'	=> $order->userId,
				'type'			=> Model_Address::TYPE_DELIVERY,
			] );
		}
		return $this;
	}
}
