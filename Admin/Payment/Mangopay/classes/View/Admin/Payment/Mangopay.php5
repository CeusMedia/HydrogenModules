<?php
class View_Admin_Payment_Mangopay extends CMF_Hydrogen_View{

	public static function ___onRegisterTab( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'admin/payment/mangopay' );				//  load words
		$context->registerTab( 'client', $words->tabs['client'], 0 );								//  register main tab
		$context->registerTab( 'seller', $words->tabs['seller'], 1 );								//  register main tab
		$context->registerTab( 'payin', $words->tabs['payins'], 2 );								//  register main tab
		$context->registerTab( 'event', $words->tabs['events'], 3 );								//  register main tab
		$context->registerTab( 'hook', $words->tabs['hooks'], 4 );									//  register main tab
//		$context->registerTab( 'wallet', $words->tabs['wallets'], 1 );								//  register main tab
	}

	public static function renderTabs( CMF_Hydrogen_Environment $env, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './admin/payment/mangopay/' );
		$env->getModules()->callHook( "AdminPaymentMangopay", "registerTabs", $tabs/*, $data*/ );	//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}

}
?>
