<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Work_Mail_Group extends View
{
	public function add(): void
	{
	}

	public function edit(): void
	{
	}

	public function index(): void
	{
	}

	public static function renderTabs( Environment $env, $current = 0 ): string
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/mail/group/' );
		$env->getModules()->callHook( "WorkMailGroup", "registerTabs", $tabs/*, $data*/ );		//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}
}
