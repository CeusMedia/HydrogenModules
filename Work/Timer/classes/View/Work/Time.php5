<?php
class View_Work_Time extends CMF_Hydrogen_View
{
/*	protected function __onInit(){
		$this->modelTimer		= new Model_Work_Timer( $this->env );
		$this->modelProject		= new Model_Project( $this->env );
	}
*/
	public function add(){}

	public function ajaxRenderDashboardPanel(){
	}

	public function edit(){}

	public function index(){}

	public static function renderTabs( CMF_Hydrogen_Environment $env, $current = 0 )
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/time/' );
		$env->getModules()->callHook( "WorkTime", "registerTabs", $tabs/*, $data*/ );						//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}
}
