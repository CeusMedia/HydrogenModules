<?php
class Controller_Mangopay_Payin extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->mangopay		= Logic_Payment_Mangopay::getInstance( $this->env );
		$this->model		= new Model_Mangopay_Payin( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
	}

	public function index( $page = 0 ){
		$limit		= 15;
		$offset		= $page * $limit;
		$conditions	= array();
		$orders		= array( 'modifiedAt' => 'DESC' );
		$limits		= array( $offset, $limit );
		$total		= $this->model->count();
		$count		= $this->model->count( $conditions );
		$pages		= ceil( $count / $limit );
		$payins		= $this->model->getAll( $conditions, $orders, $limits );
		foreach( $payins as $nr => $payin )
			$payin->user	= $this->mangopay->getUser( $payin->userId );
		$this->addData( 'payins', $payins );
		$this->addData( 'count', $count );
		$this->addData( 'pages', $pages );
		$this->addData( 'page', $page );
	}

	public function view( $payinId ){
		$payin	= $this->model->get( $payinId );
		if( !$payin ){
			$this->messenger->noteError( 'Invalid payin ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'payin', $payin );
//		$this->addData( 'page', $this->request->get( 'page' ) );
	}
}
?>
