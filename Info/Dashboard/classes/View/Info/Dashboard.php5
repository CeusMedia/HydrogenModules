<?php
class View_Info_Dashboard extends CMF_Hydrogen_View{

	public function __onInit(){}

	static public function ___onRenderContent( $env, $context, $module, $data = array() ){
		$pattern	= '/^(.*)(\[info:dashboard\])(.*)$/sU';
		$data		= (object) $data;
		while( preg_match( $pattern, $data->content ) ){
			$dashboard		= new View_Helper_Info_Dashboard( $env );
			$replacement	= "\\1".$dashboard->render()."\\3";										//  insert content of nested page...
			$data->content	= preg_replace( $pattern, $replacement, $data->content, 1  );		//  ...into page content
		}
	}

	public function ajaxRename(){}

	public function index(){
		$page	= $this->env->getPage();
		$page->js->addUrl(  $this->env->getConfig()->get( 'path.scripts' ).'InfoDashboard.js' );
		$page->js->addScriptOnReady( 'InfoDashboard.init();' );
		$page->addThemeStyle( 'module.info.dashboard.css' );
/*
//		$dashboard	= new View_Helper_Info_Dashboard( $this->env );
		$data		= (object) array( 'content' => '[info:dashboard]' );
		$this->env->captain->callHook( 'View', 'renderContent', $this, $data );
		$this->addData( 'dashboard', $data->content );
*/	}
}
?>
