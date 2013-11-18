<?php
class View_Helper_Module extends CMF_Hydrogen_View_Helper_Abstract{

	public function __construct( $env ){
		$this->setEnv( $env );
		$this->logic	= Logic_Module::getInstance( $env );
		$this->modules	= array();
		foreach( $this->logic->model->getAll() as $module )
			$this->modules[$module->id]	= $module;
	}
	
	public function renderModuleLink( $moduleId, $status = 0 ){
		$title	= $moduleId;
		if( array_key_exists( $moduleId, $this->modules ) ){
			$module	= $this->modules[$moduleId];
			$title	= htmlspecialchars( $module->title, ENT_QUOTES, 'UTF-8' );
		}
		$url		= './admin/module/viewer/'.$moduleId;
		$link		= UI_HTML_Tag::create( 'a', $title, array( 'href' => $url ) );
		$span		= UI_HTML_Tag::create( 'span', $link, array( 'class' => 'icon module module-status-'.$status ) );
		return $span;
	}
}
?>