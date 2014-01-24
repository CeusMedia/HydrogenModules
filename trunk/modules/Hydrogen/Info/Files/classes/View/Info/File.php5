<?php
class View_Info_File extends CMF_Hydrogen_View{
	public function index(){
		$this->env->getPage()->addThemeStyle( 'module.info.files.css' );
	}
}
?>
