<?php
class View_Manage_My_Provision_License_Key extends View_Manage_My_License{

	public function edit(){}

	public function index(){}

	public function view(){}

	public function __onInit(){
		$this->env->getPage()->addThemeStyle( 'module.manage.my.provision.css' );
	}
}
?>
