<?php
class View_Info_Forum extends CMF_Hydrogen_View{

	public function __onInit(){
		$pathJs	= $this->env->getConfig()->get( 'path.scripts' );
		$this->env->getPage()->addThemeStyle( 'module.info.forum.css' );
		$this->env->getPage()->js->addUrl( $pathJs.'InfoForum.js' );								//  @todo	Fix this hack
	}

	public function index(){}
	public function thread(){}
	public function topic(){}
}
?>
