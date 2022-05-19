<?php
class Hook_Catalog_Provision/* extends CMF_Hydrogen_Hook*/{

	static public function onAppDispatch( $env, $context, $module, $data = [] ){
		$request    = $env->getRequest();
		$session    = $env->getSession();
		if( $request->has( 'productId' ) )
			$session->set( 'register.productId', $request->get( 'productId' ) );
		if( $request->has( 'licenseId' ) )
			$session->set( 'register.licenseId', $request->get( 'licenseId' ) );
	}

	/**
	 *	@todo not working, needs Logic_User_Provision, find better solution!
	 */
	static public function onShopFinish( $env, $context, $module, $data = [] ){
		$logicCatalog		= Logic_Catalog_Provision::getInstance( $env );
		$logicProvision		= Logic_User_Provision::getInstance( $env );
		$logicShop			= new Logic_Shop( $env );
		$logicShopBridge	= new Logic_ShopBridge( $env );
		$order				= $logicShop->getOrder( $data['orderId'], TRUE );
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
