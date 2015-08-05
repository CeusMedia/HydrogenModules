<?php
class View_Helper_Navigation{

	protected $env;
	protected $menu;
	protected $moduleConfig;

	public function __construct( $env ){
		$this->env			= $env;
		$this->menu			= new Model_Menu( $env );
		$this->moduleConfig	= $env->getConfig()->getAll( "module.ui_navigation.", TRUE );
	}

	public function render( $scope = 'main', $class = NULL, $style = NULL ){
		$class		= $class ? $class : $this->moduleConfig->get( 'nav.class' );
		$style		= $style ? $style : $this->moduleConfig->get( 'nav.style' );
		$argments	= array( $this->env, $this->menu );
		if( !class_exists( $class ) )
			throw new RuntimeException( 'Navigation class "'.$class.'" is not existing' );
		$helper	= Alg_Object_Factory::createObject( $class, $argments );
		return $helper->render( $scope, $style );
	}
}
?>
