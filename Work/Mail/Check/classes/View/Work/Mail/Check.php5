<?php
class View_Work_Mail_Check extends CMF_Hydrogen_View
{
	public function add()
	{
	}

	public function ajaxAddress()
	{
		$html	= $this->loadTemplateFile( 'work/mail/check/ajaxAddress.php' );
		print( $html );
		exit;
	}

	public function export()
	{
	}

	public function group()
	{
	}

	public function index()
	{
	}

	public function import()
	{
	}

	public function status()
	{
	}

	public static function renderTabs( CMF_Hydrogen_Environment $env, $current = 0 )
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/mail/check/' );
		$env->getModules()->callHook( "WorkMailCheck", "registerTabs", $tabs/*, $data*/ );	//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}
}
