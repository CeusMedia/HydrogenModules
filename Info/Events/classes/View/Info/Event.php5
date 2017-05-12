<?php
class View_Info_Event extends CMF_Hydrogen_View{

	public function __onInit(){
		$pathJs     = $this->env->getConfig()->get( 'path.scripts' );
		$this->env->getPage()->js->addUrl( $pathJs.'module.info.event.js' );
		$this->env->getPage()->addThemeStyle( 'module.info.event.css' );
	}

	public function calendar(){
	}

	public function index(){
	}

	public function map(){
		$script     = 'if($(".UI_Map").size()){applyMapMarkers(".UI_Map", ".map-point");}';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	public function modalView(){
		print( $this->loadTemplateFile( 'info/event/view.modal.php' ) );
		exit;
	}

	public function view(){
	}
}
