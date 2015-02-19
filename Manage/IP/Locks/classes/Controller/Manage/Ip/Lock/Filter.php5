<?php
class Controller_Manage_IP_Lock_Filter extends CMF_Hydrogen_Controller{

	protected $logic;
	protected $messenger;

	public function __onInit(){
		$this->logic		= Logic_IP_Lock::getInstance( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_IP_Lock_Filter( $this->env );
	}

	public function activate( $filterId ){
		$this->model->edit( $filterId, array( 'status' => 1 ) );
		$this->restart( NULL, TRUE );
	}

	public function add(){
		$request	= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$data		= $request->getAll();
			$data['createdAt']	= time();
			$filterId	= $this->model->add( $data );
			$this->messenger->noteSuccess( 'Filter added.' );
			$this->restart( NULL, TRUE );
		}
		$this->setData( $request->getAll() );
		$model		= new Model_IP_Lock_Reason( $this->env );
		$this->addData( 'reasons', $model->getAll() );
	}

	public function deactivate( $filterId ){
		$this->model->edit( $filterId, array( 'status' => 0 ) );
		$this->restart( NULL, TRUE );
	}

	public function edit( $filterId ){
		$request	= $this->env->getRequest();
		$filter		= $this->model->get( $filterId );
		if( !$filter ){
			$this->messenger->notError( 'Invalid filter ID.' );
			$this->restart( NULL, FALSE );
		}
		if( $request->has( 'save' ) ){
			$data		= $request->getAll();
			$data['modifiedAt']	= time();
			$this->model->edit( $filterId, $data );
			$this->messenger->noteSuccess( 'Filter saved.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'filter', $filter );
		$model		= new Model_IP_Lock_Reason( $this->env );
		$this->addData( 'reasons', $model->getAll() );
	}

	public function index(){
		$conditions	= array();
		$orders		= array();
		$limits		= array();
		$model		= new Model_IP_Lock_Reason( $this->env );
		$filters	= $this->model->getAll( $conditions, $orders, $limits );
		foreach( $filters as $nr => $filter ){
			$filter->reason	= $model->get( $filter->reasonId );
		}
		$this->addData( 'filters', $filters );
	}

	public function remove( $filterId ){
		$request	= $this->env->getRequest();
		$filter		= $this->model->get( $filterId );
		if( !$filter ){
			$this->messenger->notError( 'Invalid filter ID.' );
			$this->restart( NULL, FALSE );
		}
		$this->model->remove( $filterId );
		$this->messenger->noteSuccess( 'Filter removed.' );
		$this->restart( NULL, TRUE );
	}
}
