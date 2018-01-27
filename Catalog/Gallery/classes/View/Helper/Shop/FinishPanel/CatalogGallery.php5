<?php
class View_Helper_Shop_FinishPanel_CatalogGallery{

	protected $env;
	protected $orderId;
	protected $options;

	public function __construct( $env ){
		$this->env		= $env;
		$this->options	= $env->getConfig()->getAll( 'module.catalog_gallery.', TRUE );
	}

	public function __toString(){
		return $this->render();
	}

	public function render(){
		if( !$this->orderId )
			throw new RuntimeException( 'No order ID set' );
		$data		= array(
			'url'		=> './catalog/gallery/downloadOrder/'.$this->orderId,
			'duration'	=> $this->options->get( 'download.duration'),
		);
		if( $this->options->get( 'download.auto' ) ){
			$this->env->getPage()->addMetaTag( 'http-equiv', 'refresh', '1; URL='.$data['url'] );
		}
		$view	= new CMF_Hydrogen_View( $this->env );
		return $view->loadContentFile( 'html/catalog/gallery/delivery.html', $data );
	}

	public function setOrderId( $orderId ){
		$this->orderId		= $orderId;
	}
}
