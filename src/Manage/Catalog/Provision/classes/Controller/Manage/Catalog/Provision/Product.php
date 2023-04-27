<?php

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Catalog_Provision_Product extends Controller
{
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Logic_Catalog_Provision $logicProvision;
	protected Model_Provision_Product $modelProduct;
	protected Model_Provision_Product_License $modelLicense;

	public function add()
	{
		if( $this->request->has( 'save' ) ){
			$data		= $this->request->getAll();
			$data['createdAt']	= time();
			$productId	= $this->modelProduct->add( $data, FALSE );
			$this->messenger->noteSuccess( 'Product added.' );
			$this->restart( 'edit/'.$productId, TRUE );
		}
		$product	= [];
		foreach( $this->modelProduct->getColumns() as $column )
			$product[$column]	= $this->request->get( $column );
		$this->addData( 'product', (object) $product );
	}

	public function edit( $productId )
	{
		if( $this->request->has( 'save' ) ){
			$data		= $this->request->getAll();
			$data['modifiedAt']	= time();
			$projectId	= $this->modelProduct->edit( $productId, $data, FALSE );
			$this->messenger->noteSuccess( 'Product saved.' );
			$this->restart( 'edit/'.$productId, TRUE );
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

	public function index()
	{
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request				= $this->env->getRequest();
		$this->logicProvision		= Logic_Catalog_Provision::getInstance( $this->env );
		$this->messenger			= $this->env->getMessenger();
		$this->modelProduct			= new Model_Provision_Product( $this->env );
		$this->modelLicense			= new Model_Provision_Product_License( $this->env );
		$this->addData( 'products', $this->logicProvision->getProducts() );
	}
}
