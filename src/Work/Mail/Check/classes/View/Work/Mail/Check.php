<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Work_Mail_Check extends View
{
	public static function renderTabs( Environment $env, $current = 0 ): string
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/mail/check/' );
		$env->getModules()->callHook( "WorkMailCheck", "registerTabs", $tabs/*, $data*/ );	//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}

	public function add()
	{
	}

	public function export(): void
	{
	}

	public function group(): void
	{
	}

	public function import(): void
	{
	}

	public function index(): void
	{
	}

	public function status(): void
	{
	}
}
