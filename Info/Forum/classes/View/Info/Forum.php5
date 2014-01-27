<?php
class View_Info_Forum extends CMF_Hydrogen_View{

	public function __onInit(){
		$this->env->getPage()->addThemeStyle( 'module.info.forum.css' );
		$this->env->getPage()->js->addUrl( 'scripts/InfoForum.js' );								//  @todo	Fix this hack
	}

	public function index(){}
	public function thread(){}
	public function topic(){}
}
?>
