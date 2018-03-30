<?php
class Controller_Catalog_Provision_Product extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->modelProduct	= new Model_Provision_Product( $this->env );
		$this->modelLicense	= new Model_Provision_Product_License( $this->env );
	}

	public function index(){
		$conditions	= array();
		$orders		= array( 'rank' => 'ASC' );
		$this->addData( 'products', $this->modelProduct->getAll( $conditions, $orders ) );
	}

	public function view( $productId ){
		$this->addData( 'product', $this->modelProduct->get( $productId ) );
		$this->addData( 'licenses', $this->modelLicense->getAllByIndex( 'productId', $productId, array( 'rank' => 'ASC' ) ) );
		$this->addData( 'productId', $productId );
	}
}
