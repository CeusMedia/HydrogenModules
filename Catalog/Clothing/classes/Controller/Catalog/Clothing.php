<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;

class Controller_Catalog_Clothing extends Controller
{
	public function index(){
	}

	public static function __onRenderServicePanels( Environment $env, $context, $module, $data = [] )
	{
		$arguments	= new Dictionary( $data );
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

	protected function __onInit()
	{
	}
}
