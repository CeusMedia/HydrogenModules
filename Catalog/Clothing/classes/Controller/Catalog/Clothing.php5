<?php
class Controller_Catalog_Clothing extends CMF_Hydrogen_Controller{

	protected function __onInit(){
	}

	public function index(){
	}

	static public function __onRenderServicePanels( $env, $context, $module, $data = array() ){
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
