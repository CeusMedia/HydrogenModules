<?php
class Controller_Info_Workshop extends CMF_Hydrogen_Controller{

	protected $model;

	public function __onInit(){
		$this->model	= new Model_Workshop( $this->env );
		$this->addData( 'pathImages', '' );
	}

	public function index(){
		$conditions	= array( 'status' => array( 1, 2 ) );
		$orders		= array( 'status' => 'ASC', 'rank' => 'ASC' );
		$this->addData( 'workshops', $this->model->getAll( $conditions, $orders ) );
	}

	public function view( $id ){
		$id	= (int) $id;
		$workshop	= $this->model->get( $id );
		if( !$workshop ){
			$this->env->getMessenger()->noteError( 'Kein Workshop unter dieser Adresse gefunden. Weiterleitung zur Übersicht.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'workshop', $this->model->get( $id ) );
	}
}

