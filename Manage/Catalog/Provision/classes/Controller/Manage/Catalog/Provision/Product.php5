<?php
class Controller_Manage_Catalog_Provision_Product extends CMF_Hydrogen_Controller{

	protected function __onInit(){
		$this->logicProvision		= Logic_Catalog_Provision::getInstance( $this->env );
		$this->request				= $this->env->getRequest();
		$this->messenger			= $this->env->getMessenger();
		$this->modelProduct			= new Model_Provision_Product( $this->env );
		$this->modelLicense			= new Model_Provision_Product_License( $this->env );
		$this->addData( 'products', $this->logicProvision->getProducts() );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$data		= $this->request->getAll();
			$data['createdAt']	= time();
			$productId	= $this->modelProduct->add( $data, FALSE );
			$this->messenger->noteSuccess( 'Product added.' );
			$this->restart( 'edit/'.$productId, TRUE );
		}
		$product	= array();
		foreach( $this->modelProduct->getColumns() as $column )
			$product[$column]	= $this->request->get( $column );
		$this->addData( 'product', (object) $product );
	}

	public function edit( $productId ){
		if( $this->request->has( 'save' ) ){
			$data		= $this->request->getAll();
			$data['modifiedAt']	= time();
			$projectId	= $this->modelProduct->edit( $productId, $data, FALSE );
			$this->messenger->noteSuccess( 'Product saved.' );
			$this->restart( NULL, TRUE );
		}
		$licenses	= $this->logicProvision->getProductLicenses( $productId );
		foreach( $licenses as $license ){
//			$license->product	= $this->logicProvision->getProduct( $license->productId );
			$license->count		= 0;//$this->logicProvision->countUserLicensesByProductLicense( $license->productLicenseId );
		}
		$this->addData( 'licenses', $licenses );
		$this->addData( 'productId', $productId );
		$this->addData( 'product', $this->modelProduct->get( $productId ) );
	}

	public function index(){
	}
}
