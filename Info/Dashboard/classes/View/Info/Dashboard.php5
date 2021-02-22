<?php
class View_Info_Dashboard extends CMF_Hydrogen_View
{
	public function index()
	{
		$page	= $this->env->getPage();
		$page->js->addUrl(  $this->env->getConfig()->get( 'path.scripts' ).'InfoDashboard.js' );
		$page->js->addScriptOnReady( 'InfoDashboard.init();' );
		$page->addThemeStyle( 'module.info.dashboard.css' );
	}

	protected function __onInit()
	{
	}
}
