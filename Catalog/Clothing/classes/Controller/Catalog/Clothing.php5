<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;

class Controller_Catalog_Clothing extends Controller{

	protected function __onInit(){
	}

	public function index(){
	}

	static public function __onRenderServicePanels( Environment $env, $context, $module, $data = [] ){
		$arguments	= new ADT_List_Dictionary( $data );
		if( $orderId = $arguments->get( 'orderId' ) ){
			$view		= new View_Catalog_Clothing( $env );
			$helper		= new View_Helper_Shop_FinishPanel_CatalogClothing( $env );
			$helper->setOrderId( $orderId );
			$context->registerServicePanel(
				'CatalogClothing',
				$helper,
				2
			);
		}
	}
}
