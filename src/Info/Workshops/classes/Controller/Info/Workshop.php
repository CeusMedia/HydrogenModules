<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Info_Workshop extends Controller
{
	protected $model;

	public function index()
	{
		$conditions	= ['status' => [1, 2] ];
		$orders		= ['status' => 'ASC', 'rank' => 'ASC'];
		$this->addData( 'workshops', $this->model->getAll( $conditions, $orders ) );
	}

	public function view( $id )
	{
		$id	= (int) $id;
		$workshop	= $this->model->get( $id );
		if( !$workshop ){
			$this->env->getMessenger()->noteError( 'Kein Workshop unter dieser Adresse gefunden. Weiterleitung zur Übersicht.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'workshop', $this->model->get( $id ) );
	}

	protected function __onInit(): void
	{
		$this->model	= new Model_Workshop( $this->env );
		$this->addData( 'pathImages', '' );
	}
}
