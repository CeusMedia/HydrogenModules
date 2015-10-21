<?php
class Controller_Manage_Catalog extends CMF_Hydrogen_Controller{

	/**	@var		Logic_Catalog		$logic */
	protected $logic;
	protected $messenger;
	protected $request;
	protected $session;

	protected function __onInit(){
		$this->logic		= new Logic_Catalog( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
	}

	public function index(){
		$this->restart( 'article', TRUE );
	}
}
?>
