<?php
class Hook_Catalog_Provision/* extends CMF_Hydrogen_Hook*/{

	/**
	 *	@todo not working, needs Logic_User_Provision, find better solution!
	 */
	static public function onShopFinish( $env, $context, $module, $data = array() ){
		$logicProvision		= Logic_Provision::getInstance( $env );
		$logicShop			= new Logic_Shop( $env );
		$logicShopBridge	= new Logic_ShopBridge( $env );
		$order				= $logicShop->getOrder( $data['orderId'], TRUE );
		$bridgeId			= $logicShopBridge->getBridgeId( 'ProductLicense' );
		foreach( $order->positions as $position ){
			if( $position->bridgeId != $bridgeId )
				continue;
			$license	= $logicProvision->getProductLicense( $position->articleId );
			$logicProvision->addUserLicense( $order->userId, $license->productLicenseId, TRUE );
			$logicProvision->enableNextUserLicenseKeyForProduct( $order->userId, $license->productId );
		}
	}
}
