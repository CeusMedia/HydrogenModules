<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

class View_Helper_Shop_FinishPanel_CatalogGallery
{
	protected WebEnvironment $env;
	protected Dictionary $options;
	protected ?string $orderId			= NULL;

	public function __construct( WebEnvironment $env )
	{
		$this->env		= $env;
		$this->options	= $env->getConfig()->getAll( 'module.catalog_gallery.', TRUE );
	}

	public function __toString(): string
	{
		return $this->render();
	}

	public function render(): string
	{
		if( !$this->orderId )
			throw new RuntimeException( 'No order ID set' );
		$data		= [
			'url'		=> './catalog/gallery/downloadOrder/'.$this->orderId,
			'duration'	=> $this->options->get( 'download.duration'),
		];
		if( $this->options->get( 'download.auto' ) ){
			$this->env->getPage()->addMetaTag( 'http-equiv', 'refresh', '1; URL='.$data['url'] );
		}
		$view	= new View( $this->env );
		return $view->loadContentFile( 'html/catalog/gallery/delivery.html', $data );
	}

	public function setOrderId( string $orderId ): self
	{
		$this->orderId		= $orderId;
		return $this;
	}
}
