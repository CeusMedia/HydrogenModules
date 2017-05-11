<?php
class View_Info_Event extends CMF_Hydrogen_View{

	public function calendar(){
		$this->env->getPage()->addThemeStyle( 'module.info.event.css' );
	}

	public function index(){
		$this->restart( 'calender', TRUE );
	}

	public function modalView(){
		print( $this->loadTemplateFile( 'info/event/view.modal.php' ) );
		exit;
	}

	public function view(){
	}
}
