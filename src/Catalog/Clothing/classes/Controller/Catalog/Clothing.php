<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;

class Controller_Catalog_Clothing extends Controller
{
	public function index(): void
	{
	}

	public static function __onRenderServicePanels( Environment $env, object $context, object $module, array & $payload ): void
	{
		/** @var Environment\Web $env */
		$arguments	= new Dictionary( $payload );
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

	protected function __onInit(): void
	{
	}
}
