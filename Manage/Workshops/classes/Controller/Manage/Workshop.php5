<?php
class Controller_Manage_Workshop extends CMF_Hydrogen_Controller{

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_Workshop( $this->env );

		$moduleConfigTinyMce	= $this->env->getConfig()->getAll( 'module.js_tinymce.auto.', TRUE );
		$tinyMceAutoClass		= preg_replace( '/^(textarea)?\./i', '', $moduleConfigTinyMce->get( 'selector' ) );
		$this->addData( 'tinyMceAutoClass', $tinyMceAutoClass );
		$this->addData( 'tinyMceAutoMode', $moduleConfigTinyMce->get( 'mode' ) );
	}

	public function add(){
		if( $this->request->isPost() && $this->request->has( 'save' ) ){
			$data	= array_merge( $this->request->getAll(), array(
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			) );
			$workshopId	= $this->model->add( $data, FALSE );
			$this->messenger->noteSuccess( 'Added.' );
			$this->restart( './edit/'.$workshopId );
		}
		$data	= array();
		foreach( $this->model->getColumns() as $column )
			if( !in_array( $column, array( 'workshopId', 'createdAt', 'modifiedAt' ) ) )
				$data[$column]	= NULL;
		$defaults	= array( 'status' => 0, 'rank' => 3 );
		$given	= array_intersect_key( $this->request->getAll(), $data );
		$this->addData( 'workshop', (object) array_merge( $data, $defaults, $given ) );
	}

	public function edit( $workshopId ){
		$workshop	= $this->model->get( $workshopId );
		if( !$workshop ){
			$this->messenger->noteError( 'Invalid workshop ID.' );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->isPost() && $this->request->has( 'save' ) ){
			$this->model->edit( $workshopId, $this->request->getAll(), FALSE );
			$this->messenger->noteSuccess( 'Updated.' );
			$this->restart( './edit/'.$workshopId );
		}
		$this->addData( 'workshop', $workshop );
	}

	public function index(){
		$this->addData( 'workshops', $this->model->getAll() );
	}

	public function remove( $workshopId ){
		$workshop	= $this->model->get( $workshopId );
		if( !$workshop ){
			$this->messenger->noteError( 'Invalid workshop ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->model->remove( $workshopId );
		$this->messenger->noteSuccess( 'Removed.' );
		$this->restart( NULL, TRUE );
	}
}
