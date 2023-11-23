<?php

use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Catalog_Provision_Product_License extends Controller
{
	protected ?MessengerResource $messenger;
	protected Request $request;
	protected Model_Provision_Product_License $modelLicense;
	protected Model_Provision_Product $modelProduct;
	protected Logic_Catalog_Provision $logicProvision;

	public function add( $productId = NULL ): void
	{
		if( !$productId ){
			$this->messenger->noteError( "Please select a product,first!" );
			$this->restart( './manage/catalog/provision/product' );
		}
		if( $this->request->has( 'save' ) ){
			$data		= $this->request->getAll();
			$data['users']		= (int) $data['users'];
			$data['price']		= (float) $data['price'];
			$data['rank']		= (int) $data['rank'];
			$data['productId']	= $productId;
			$data['createdAt']	= time();
			$licenseId	= $this->modelLicense->add( $data, FALSE );
			$this->messenger->noteSuccess( 'Product license added.' );
			$this->restart( './manage/catalog/provision/product/edit/'.$productId );
		}
		$license	= [];
		foreach( $this->modelLicense->getColumns() as $column )
			$license[$column]	= $this->request->get( $column );
		$this->addData( 'license', (object) $license );
		$this->addData( 'productId', $productId );
		$this->addData( 'product', $this->logicProvision->getProduct( $productId ) );
	}

	public function edit( $licenseId  ): void
	{
		$license	= $this->modelLicense->get( $licenseId );
		if( !$license ){
			$this->messenger->noteError( 'Invalid license ID.' );
			$this->restart( 'manage/catalog/provision/product' );
		}
		if( $this->request->has( 'save' ) ){
			$data		= $this->request->getAll();
			$data['modifiedAt']	= time();
			$projectId	= $this->modelLicense->edit( $licenseId, $data, FALSE );
			$this->messenger->noteSuccess( 'Product license saved.' );
			$this->restart( './manage/catalog/provision/product/edit/'.$license->productId );
		}
		$this->addData( 'license', $license );
		$this->addData( 'productId', $license->productId );
		$this->addData( 'product', $this->modelProduct->get( $license->productId ) );
		$this->addData( 'licenses', $this->modelLicense->getAll() );
	}

	public function index( $productId = NULL ): void
	{
		if( !$productId ){
			$this->messenger->noteError( "Please select a product,first!" );
			$this->restart( './manage/catalog/provision/product' );
		}
		$licenses	= $this->modelLicense->getAll();
		foreach( $licenses as $license ){
			$license->product	= $this->modelProduct->get( $license->productId );
			$license->count		= $this->logicProvision->countUserLicensesByProductLicense( $license->productLicenseId );
		}
		$this->addData( 'licenses', $licenses );
		$this->addData( 'product', $this->modelProduct->get( $productId ) );
	}

	protected function __onInit(): void
	{
		$this->request				= $this->env->getRequest();
		$this->messenger			= $this->env->getMessenger();
		$this->logicProvision		= Logic_Catalog_Provision::getInstance( $this->env );
		$this->modelProduct			= new Model_Provision_Product( $this->env );
		$this->modelLicense			= new Model_Provision_Product_License( $this->env );
		$this->addData( 'products', $this->logicProvision->getProducts() );
	}
}
