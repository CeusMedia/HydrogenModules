<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Shop extends View
{
	public static function ___onRegisterTab( Environment $env, $context, $module, $data )
	{
		$words	= (object) $env->getLanguage()->getWords( 'manage/shop' );									//  load words
		$context->registerTab( '', $words->tabs['dashboard'], 0 );											//  register orders tab
	}

	public static function renderTabs( Environment $env, $current = 0 )
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './manage/shop/' );
		$env->getModules()->callHook( "ManageShop", "registerTabs", $tabs/*, $data*/ );						//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}

	public function index()
	{
	}
}
