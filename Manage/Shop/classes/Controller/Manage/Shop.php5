<?php
class Controller_Manage_Shop extends CMF_Hydrogen_Controller{
	
	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
	}

	public function index(){
	}

	public function setTab( $newsletterId, $tabKey ){
		$this->session->set( 'manage.shop.tab', $tabKey );
#		$this->restart( './work/newsletter/edit/'.$newsletterId );
	}
}
?>
