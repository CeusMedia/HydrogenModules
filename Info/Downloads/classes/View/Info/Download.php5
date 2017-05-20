<?php
class View_Info_Download extends CMF_Hydrogen_View{

	public function index(){
		$this->env->getPage()->addThemeStyle( 'module.info.downloads.css' );
	}

	public function view(){}
}
?>
