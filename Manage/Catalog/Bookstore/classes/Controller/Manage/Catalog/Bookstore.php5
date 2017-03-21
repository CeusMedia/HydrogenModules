<?php
class Controller_Manage_Catalog_Bookstore extends CMF_Hydrogen_Controller{

	/**	@var		Logic_Catalog_Bookstore		$logic */
	protected $logic;
	protected $messenger;
	protected $request;
	protected $session;

	protected function __onInit(){
		$this->logic		= new Logic_Catalog_Bookstore( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
	}

	public function index(){
		$this->restart( 'article', TRUE );
	}
}
?>
