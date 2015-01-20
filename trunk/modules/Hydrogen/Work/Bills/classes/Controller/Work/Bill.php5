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

	public function filter( $reset = NULL ){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();

		if( $reset ){
			$session->remove( 'filter_work_bill_id' );
			$session->remove( 'filter_work_bill_term' );
			$session->remove( 'filter_work_bill_type' );
			$session->remove( 'filter_work_bill_status' );
			$session->remove( 'filter_work_bill_start' );
			$session->remove( 'filter_work_bill_end' );
			$session->remove( 'filter_work_bill_limit' );
			$session->remove( 'filter_work_bill_order' );
			$session->remove( 'filter_work_bill_direction' );
		}
		else{
			$session->set( 'filter_work_bill_id', $request->get( 'id' ) );
			$session->set( 'filter_work_bill_term', $request->get( 'term' ) );
			$session->set( 'filter_work_bill_type', $request->get( 'type' ) );
			$session->set( 'filter_work_bill_status', $request->get( 'status' ) );
			$session->set( 'filter_work_bill_start', $request->get( 'start' ) );
			$session->set( 'filter_work_bill_end', $request->get( 'end' ) );
			$session->set( 'filter_work_bill_limit', $request->get( 'limit' ) );
			$session->set( 'filter_work_bill_order', $request->get( 'order' ) );
			$session->set( 'filter_work_bill_direction', $request->get( 'direction' ) );
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = NULL ){
		$session	= $this->env->getSession();
		$filters	= $session->getAll( 'filter_work_bill_', TRUE );

		if( !$filters->get( 'order' ) )
			$session->set( 'filter_work_bill_order', 'date' );
		if( !$filters->get( 'direction' ) )
			$session->set( 'filter_work_bill_direction', 'ASC' );
		if( !strlen( $filters->get( 'limit' ) ) )
			$session->set( 'filter_work_bill_limit', 10 );
		if( !is_array( $filters->get( 'type' ) ) || !count( $filters->get( 'type' ) ) )
			$session->set( 'filter_work_bill_type', array( 0, 1 ) );
		if( !is_array( $filters->get( 'status' ) ) || !count( $filters->get( 'status' ) ) )
			$session->set( 'filter_work_bill_status', array( 0, 1 ) );
		$filters	= $session->getAll( 'filter_work_bill_', TRUE );

		$conditions	= array(
			'userId'	=> $this->userId,
		);
		if( $filters->get( 'id' ) )
			$conditions['billId']	= $filters->get( 'id' );
		if( $filters->get( 'term' ) )
			$conditions['title']	= '%'.$filters->get( 'term' ).'%';
		if( count( $filters->get( 'status' ) ) )
			$conditions['status']	= $filters->get( 'status' );
		if( count( $filters->get( 'type' ) ) )
			$conditions['type']		= $filters->get( 'type' );
		if( $filters->get( 'start' ) || $filters->get( 'fend' ) ){
			if( $filters->get( 'start' ) && $filters->get( 'end' ) ){
				$start		= strtotime( $filters->get( 'start' )." 00:00:00" );
				$end		= strtotime( $filters->get( 'end' )." 23:59:59" );
				$duration	= $end - $start;
				$days		= round( $duration / ( 24 * 60 * 60 ) );
				if( $duration > 0 && $days > 0 ){
					$conditions['date']	= array();
					for( $i=0; $i<$days; $i++ )
						$conditions['date'][]	= date( "Ymd", $start + ( $i * 24 * 60 * 60 ) );
				}
				else{
					throw new InvalidArgumentException( '!!!' );									//  @todo kriss: handle invalid start/end date
				}
			}
			else{
				if( $filters->get( 'start' ) )
					$conditions['date']	= '>='.date( "Ymd", strtotime( $filters->get( 'start' ) ) );
				if( $filters->get( 'end' ) )
					$conditions['date']	= '<='.date( "Ymd", strtotime( $filters->get( 'end' ) ) );
			}
		}

		$total		= $this->model->count( $conditions );
		$limit		= max( 10, $session->get( 'filter_work_bill_limit' ) );
		$offset		= $limit * $page;
		$orders		= array( 'date' => 'ASC' );
		$bills		= $this->model->getAll( $conditions, $orders, array( $offset, $limit ) );
		$this->addData( 'bills', $bills );
		$this->addData( 'total', $total );
		$this->addData( 'page', (int) $page );
		$this->addData( 'filters', $session->getAll( 'filter_work_bill_', TRUE ) );
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

