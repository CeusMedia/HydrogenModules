<?php
class View_Info_Dashboard{

	public function __onInit(){}

	public function index(){
		$dashboard	= new View_Helper_Info_Dashboard( $env );
		$data		= array( 'content' => '[info:dashboard]' );
		$env->captain->callHook( 'View', 'renderContent', $this, $data );
		$this->addData( 'dashboard', $data['content'] );
	}

	static public function ___onRenderContent( $env, $context, $module, $data = array() ){
		while( preg_match( "/\[info:dasboard\]/", $data['content'] ){
			$dashboard	= new View_Helper_Info_Dashboard( $env );
			$dashboard	= $dashboard->render();
			$data['content']	= preg_replace( "/\[info:dasboard\]/", $dashboard, $data['content'], 1 );
		}
	}
}
?>
