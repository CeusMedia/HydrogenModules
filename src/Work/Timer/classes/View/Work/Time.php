<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Work_Time extends View
{
	protected Model_Work_Timer $modelTimer;
	protected Model_Project $modelProject;

	public function add()
	{
	}

	public function ajaxRenderDashboardPanel()
	{
	}

	public function edit()
	{
	}

	public function index()
	{
	}

	public static function renderTabs( Environment $env, $current = 0 )
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/time/' );
		$env->getModules()->callHook( "WorkTime", "registerTabs", $tabs/*, $data*/ );						//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}

/*	protected function __onInit(): void
	{
		$this->modelTimer		= new Model_Work_Timer( $this->env );
		$this->modelProject		= new Model_Project( $this->env );
	}
*/
}
