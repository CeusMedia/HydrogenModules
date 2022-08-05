<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_User_Provision_Product extends Controller{

	protected $prefixSession		= 'filter_manage_user_provision_product_';
	protected $filters				= array( 'productId', 'productLicenseId', 'status' );

	protected function __onInit(){
		$this->request				= $this->env->getRequest();
		$this->session				= $this->env->getSession();
		$this->messenger			= $this->env->getMessenger();
		$this->logicProvision		= Logic_User_Provision::getInstance( $this->env );
//		$this->modelUser			= new Model_User( $this->env );
		$this->modelLicense			= new Model_Provision_User_License( $this->env );
		$this->addData( 'products', $this->logicProvision->getProducts() );
	}

/*	public function add( $productId = NULL ){
		if( !$productId ){
			$this->messenger->noteError( "Please select a product,first!" );
			$this->restart( './manage/user/provision/product' );
		}
		if( $this->request->has( 'save' ) ){
			$data		= $this->request->getAll();
			$data['productId']	= $productId;
			$data['createdAt']	= time();
			$licenseId	= $this->modelLicense->add( $data, TRUE );
			$this->messenger->noteSuccess( 'Product license added.' );
			$this->restart( './manage/user/provision/product/edit/'.$productId );
		}
		$license	= [];
		foreach( $this->modelLicense->getColumns() as $column )
			$license[$column]	= $this->request->get( $column );
		$this->addData( 'license', (object) $license );
		$this->addData( 'productId', $productId );
		$this->addData( 'product', $this->logicProvision->getProduct( $productId ) );
	}*/
/*
	public function edit( $productId, $licenseId ){
		if( !$productId && $licenseId ){
			$license	= $this->modelLicense->get( $licenseId );
			$productId	= $license->productId;
		}

		if( $this->request->has( 'save' ) ){
			$data		= $this->request->getAll();
			$data['modifiedAt']	= time();
			$projectId	= $this->modelLicense->edit( $licenseId, $data, TRUE );
			$this->messenger->noteSuccess( 'Product license saved.' );
			$this->restart( './manage/catalog/provision/product/edit/'.$productId );
		}
		$license	= $this->modelLicense->get( $licenseId );
		$this->addData( 'license', $license );
		$this->addData( 'productId', $productId );
		$this->addData( 'product', $this->logicProvision->getProduct( $productId ) );
		$this->addData( 'licenses', $this->modelLicense->getAll() );

		$userLicenses	= $this->logicProvision->getUserLicenseKeysFromUserLicense( $licenseId );
		$this->addData( 'userLicenses', $userLicenses );
	}
*/
	public function filter( $reset = NULL ){
		if( $reset ){
			foreach( $this->filters as $filter )
				$this->session->remove( $this->prefixSession.$filter );
		}
		foreach( $this->filters as $filter )
			$this->session->set( $this->prefixSession.$filter, $this->request->get( $filter ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $page = NULL ){
		$indices	= [];
		$columns	= $this->modelLicense->getColumns();
		foreach( $this->filters as $filter ){
			$filterValue	= $this->session->get( $this->prefixSession.$filter );
			$this->addData( 'filter'.ucfirst( $filter ), $filterValue );
			if( in_array( $filter, $columns ) && strlen( trim( $filterValue ) ) )
				$indices[$filter]	= $filterValue;
		}

		$productLicenses	= [];
		if( $filterProductId = $this->session->get( $this->prefixSession.'productId' ) ){
			$productLicenses	= $this->logicProvision->getProductLicenses( $filterProductId );
		}
		$this->addData( 'productLicenses', $productLicenses );

		$limit		= 2;
		$orders		= [];
		$limits		= array( $limit, floor( $page / $limit ) );
		$licenses	= $this->modelLicense->getAll( $indices, $orders );
		$total		= $this->modelLicense->count( $indices );
		foreach( $licenses as $license ){
			$license->license	= $this->logicProvision->getProductLicense( $license->productLicenseId );
			$license->product	= $this->logicProvision->getProduct( $license->productId );
			$license->count		= $this->logicProvision->countUserLicensesByProductLicense( $license->productLicenseId );
		}
		$this->addData( 'licenses', $licenses );
		$this->addData( 'page', $page );
		$this->addData( 'total', $total );
		$this->addData( 'limit', $limit );
	}
}
