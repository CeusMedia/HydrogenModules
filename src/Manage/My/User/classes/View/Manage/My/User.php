<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Manage_My_User extends View
{
	public function index()
	{
	}

	public function remove()
	{
	}

	public static function renderTabs( Environment $env, $current = 0 )
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './manage/my/user/' );
		$env->getModules()->callHook( "MyUser", "registerTabs", $tabs );							//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}
}
