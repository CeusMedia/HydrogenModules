<?php
class View_Info_Newsletter extends CMF_Hydrogen_View{

	public function index(){
		$script	= 'Module_Info_Newletter_Form.init();';
		$this->env->getPage()->js->addScriptOnReady( $script );
		$this->env->getPage()->js->addModuleFile( 'module.info.newsletter.js' );
	}

	public function view(){
	}

	public function register(){
	}

	public function unregister(){
	}

	public function edit(){
	}

	public function preview(){
	}
}
?>
