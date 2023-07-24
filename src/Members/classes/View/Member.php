<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Member extends View
{
	public static function ___onRegisterTab( Environment $env, $context, $module, $payload )
	{
		$words	= (object) $env->getLanguage()->getWords( 'member' );			//  load words
		$context->registerTab( '', $words->tabs['index'], 0 );					//  register main tab
		$context->registerTab( 'search', $words->tabs['search'], 1 );			//  register main tab
	}

	public static function renderTabs( Environment $env, $current = 0 )
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './member/' );
		$env->getModules()->callHook( "Member", "registerTabs", $tabs/*, $data*/ );	//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}

	public function index()
	{
	}

	public function search()
	{
	}

	public function view()
	{
	}

}
