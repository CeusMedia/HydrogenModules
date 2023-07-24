<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Catalog_Provision extends Hook
{
	public static function onAppDispatch( Environment $env, object $context, object $module, array & $payload )
	{
		$request	= $env->getRequest();
		$session	= $env->getSession();
		if( $request->has( 'productId' ) )
			$session->set( 'register.productId', $request->get( 'productId' ) );
		if( $request->has( 'licenseId' ) )
			$session->set( 'register.licenseId', $request->get( 'licenseId' ) );
	}

	/**
	 *	@todo not working, needs Logic_User_Provision, find better solution!
	 */
	public static function onShopFinish( $env, object $context, object $module, array & $payload )
	{
		$logicCatalog		= Logic_Catalog_Provision::getInstance( $env );
		$logicProvision		= Logic_User_Provision::getInstance( $env );
		$logicShop			= new Logic_Shop( $env );
		$logicShopBridge	= new Logic_ShopBridge( $env );
		$order				= $logicShop->getOrder( $payload['orderId'], TRUE );
		$bridgeId			= $logicShopBridge->getBridgeId( 'Provision' );
		foreach( $order->positions as $position ){
			if( $position->bridgeId != $bridgeId )
				continue;
			$license	= $logicCatalog->getProductLicense( $position->articleId );
			$logicProvision->addUserLicense( $order->userId, $license->productLicenseId, TRUE );
			$logicProvision->enableNextUserLicenseKeyForProduct( $order->userId, $license->productId );
		}
	}
}
