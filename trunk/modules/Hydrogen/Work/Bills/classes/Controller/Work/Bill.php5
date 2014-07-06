<?php
class Controller_Work_Bill extends CMF_Hydrogen_Controller{

	protected $model;
	protected $userId;

	protected function __onInit(){
		$this->model	= new Model_Bill( $this->env );
		$this->userId	= $this->env->getSession()->get( 'userId' );
	}

	public function add(){
		$request	= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$data	= $request->getAll();
			$data['userId']	= $this->userId;
			if( !strlen( trim( $request->get( 'title' ) ) ) )
				$this->env->getMessenger()->noteError( 'Der Titel fehlt.' );
			if( !strlen( trim( $request->get( 'price' ) ) ) )
				$this->env->getMessenger()->noteError( 'Der Betrag fehlt.' );
			if( !strlen( trim( $request->get( 'date' ) ) ) )
				$this->env->getMessenger()->noteError( 'Das Datum der Fälligkeit fehlt.' );
			if( !$this->env->getMessenger()->gotError() ){
				$data['date']	= date( 'Ymd', strtotime( $data['date'] ) );
				$this->model->add( $data );
				$this->env->getMessenger()->noteSuccess( 'Gespeichert.' );
				$this->restart( NULL, TRUE );
			}
		}
	}

	public function edit( $billId ){
		$request	= $this->env->getRequest();
		$bill	= $this->model->get( $billId );
		if( !$bill ){
			$this->env->getMessenger()->noteError( 'Invalid bill ID: '.$billId );
			$this->restart( NULL, TRUE );
		}
		if( $request->has( 'save' ) ){
			$data	= $request->getAll();
			if( !strlen( trim( $request->get( 'title' ) ) ) )
				$this->env->getMessenger()->noteError( 'Der Titel fehlt.' );
			if( !strlen( trim( $request->get( 'price' ) ) ) )
				$this->env->getMessenger()->noteError( 'Der Betrag fehlt.' );
			if( !strlen( trim( $request->get( 'date' ) ) ) )
				$this->env->getMessenger()->noteError( 'Das Datum der Fälligkeit fehlt.' );
			if( !$this->env->getMessenger()->gotError() ){
				$data['date']	= date( 'Ymd', strtotime( $data['date'] ) );
				$this->model->edit( $billId, $data );
				$this->env->getMessenger()->noteSuccess( 'Gespeichert.' );
				$this->restart( NULL, TRUE );
			}
		}
		$this->addData( 'bill', $bill );
	}

	public function index(){
	}

	public function graph(){
		$this->addData( 'userId', $this->userId );
	}

	public function setStatus( $billId, $status ){
		$from	= $this->env->getRequest()->get( 'from' );
		$this->model->edit( $billId, array( 'status' => $status ) );
		$this->restart( $from ? $from : './work/bill' );
	}

	public function remove(){
	}
}

