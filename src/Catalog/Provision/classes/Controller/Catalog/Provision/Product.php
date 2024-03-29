<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Catalog_Provision_Product extends Controller
{
	protected Model_Provision_Product $modelProduct;
	protected Model_Provision_Product_License $modelLicense;
	protected Logic_Catalog_Provision $logicCatalog;

	public function index( $productId = NULL ): void
	{
		if( !is_null( $productId ) && strlen( trim( $productId ) ) )
			$this->restart( 'view/'.$productId );
		$conditions	= [];
		$orders		= ['rank' => 'ASC'];
		$products	= $this->modelProduct->getAll( $conditions, $orders );
		foreach( $products as $nr => $product ){
			$licenses	= $this->modelLicense->getAllByIndices( [
				'productId'	=> $product->productId,
			], ['rank' => 'ASC'] );
			if( !$licenses )
				unset( $products[$nr] );
		}
		$this->addData( 'products', $products );
	}

	public function license( $licenseId ): void
	{
		$licenseId		= (int) $licenseId;
		$license		= $this->modelLicense->get( $licenseId );
		$product		= $this->modelProduct->get( $license->productId );
		$this->addData( 'license', $license );
		$this->addData( 'product', $product );
		$this->addData( 'licenses', $this->modelLicense->getAllByIndex( 'productId', $product->productId, ['rank' => 'ASC'] ) );
		$this->addData( 'licenseId', $licenseId );
	}

	public function view( $productId ): void
	{
		$productId		= (int) $productId;
		$this->addData( 'product', $this->modelProduct->get( $productId ) );
		$this->addData( 'licenses', $this->modelLicense->getAllByIndex( 'productId', $productId, ['rank' => 'ASC'] ) );
		$this->addData( 'productId', $productId );
	}

	protected function __onInit(): void
	{
		$this->modelProduct	= new Model_Provision_Product( $this->env );
		$this->modelLicense	= new Model_Provision_Product_License( $this->env );
		$this->logicCatalog	= new Logic_Catalog_Provision( $this->env );
		$this->addData( 'logic', $this->logicCatalog );
	}
}
