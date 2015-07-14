<?php
class Controller_Manage_IP_Lock_Reason extends CMF_Hydrogen_Controller{

	protected $logic;
	protected $messenger;

	public function __onInit(){
		$this->logic		= Logic_IP_Lock::getInstance( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_IP_Lock_Reason( $this->env );
	}

	public function activate( $reasonId ){
		$this->model->edit( $reasonId, array( 'status' => 1 ) );
		$this->restart( NULL, TRUE );
	}

	public function add(){
		$request	= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$data		= $request->getAll();
			$data['createdAt']	= time();
			$reasonId	= $this->model->add( $data );
			$this->messenger->noteSuccess( 'Reason added.' );
			$this->restart( NULL, TRUE );
		}
		$this->setData( $request->getAll() );
	}

	public function deactivate( $reasonId ){
		$this->model->edit( $reasonId, array( 'status' => 0 ) );
		$this->restart( NULL, TRUE );
	}

	public function edit( $reasonId ){
		$request	= $this->env->getRequest();
		$reason		= $this->model->get( $reasonId );
		if( !$reason ){
			$this->messenger->notError( 'Invalid reason ID.' );
			$this->restart( NULL, FALSE );
		}
		if( $request->has( 'save' ) ){
			$data		= $request->getAll();
			$data['modifiedAt']	= time();
			$this->model->edit( $reasonId, $data );
			$this->messenger->noteSuccess( 'Reason saved.' );
			$this->restart( NULL, TRUE );
		}
		$reason->filters	= $this->logic->getFiltersOfReason( $reason->reasonId );
		$this->addData( 'reason', $reason );
	}

	public function index(){
		$conditions	= array();
		$orders		= array();
		$limits		= array();
		$reasons	= $this->model->getAll( $conditions, $orders, $limits );
		$model		= new Model_IP_Lock_Filter( $this->env );
		foreach( $reasons as $nr => $reason ){
			$reason->filters	= $this->logic->getFiltersOfReason( $reason->reasonId );
		}
		$this->addData( 'reasons', $reasons );
	}

	public function remove( $reasonId ){
		$request	= $this->env->getRequest();
		$reason		= $this->model->get( $reasonId );
		if( !$reason ){
			$this->messenger->noteError( 'Invalid reason ID.' );
			$this->restart( NULL, FALSE );
		}
		$this->model->remove( $reasonId );
		$this->messenger->noteSuccess( 'Reason removed.' );
		$this->restart( NULL, TRUE );
	}
}
