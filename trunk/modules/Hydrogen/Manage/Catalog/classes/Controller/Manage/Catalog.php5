<?php
class Controller_Manage_Catalog extends CMF_Hydrogen_Controller{

	/**	@var		Logic_Catalog		$logic */
	protected $logic;

	protected function __onInit(){
		$this->logic		= new Logic_Catalog( $this->env );
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
	}

	public function index(){
	}

	public function setTab( $newsletterId, $tabKey ){
		$this->session->set( 'manage.catalog.tab', $tabKey );
	}
}
?>
