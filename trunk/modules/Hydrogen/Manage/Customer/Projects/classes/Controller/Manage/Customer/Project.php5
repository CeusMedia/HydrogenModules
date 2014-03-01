<?php
class Controller_Manage_Customer_Project extends CMF_Hydrogen_Controller{

	protected $messenger;
	protected $modelCustomer;
	protected $logic;

	public function __onInit(){
		$this->messenger		= $this->env->getMessenger();
		$this->modelCustomer	= new Model_Customer( $this->env );
		$this->addData( 'useRatings', $this->env->getModules()->has( 'Manage_Customer_Rating' ) );
		$this->addData( 'useMap', $this->env->getModules()->has( 'UI_Map' ) );
		$this->logic	= Logic_CustomerProject::getInstance( $this->env );
	}

	public function index( $customerId ){
		$this->addData( 'projects', $this->logic->getProjects( $customerId ) );
		$this->addData( 'customerId', $customerId );
		$this->addData( 'customer', $this->modelCustomer->get( $customerId ) );
	}
}
