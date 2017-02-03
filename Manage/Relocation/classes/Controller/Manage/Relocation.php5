<?php
class Controller_Manage_Relocation extends CMF_Hydrogen_Controller{

	protected $model;
	protected $messenger;
	protected $request;
	protected $session;
	protected $filterSessionPrefix		= 'filter-manage-relocation-';
	protected $shortcut;

	public function __onInit(){
		$this->model		= new Model_Relocation( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();

		$frontend	= Logic_Frontend::getInstance( $this->env );
		$moduleConfig	= $frontend->getModuleConfigValues( 'Info_Relocation' );

		if( $moduleConfig['shortcut'] )
			$this->shortcut		= preg_replace( "/^[^a-z]+([a-z]+)[^a-z]+$/", "\\1", $moduleConfig['shortcut.source'] );
	}

	protected function checkRelocation( $relocationId ){
		$words		= (object) $this->getWords( 'msg' );
		$relocation	= $this->model->get( $relocationId );
		if( !$relocation ){
			$this->messenger->noteError( $words->errorIdInvalid );
			$this->restart( NULL, TRUE );
		}
		return $relocation;
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$words	= (object) $this->getWords( 'msg' );
			$title	= $this->request->get( 'title' );
			$url	= $this->request->get( 'url' );
			if( $this->model->countByIndex( 'title', $title ) ){
				$this->messenger->noteError( $words->errorTitleAlreadyExists, $title );
			}
			else if( $this->model->countByIndex( 'url', $url ) ){
				$this->messenger->noteError( $words->errorUrlAlreadyExists, $url );
			}
			else{
				$relocationId	= $this->model->add( array(
					'status'	=> $this->request->get( 'status' ),
					'title'		=> $title,
					'url'		=> $url,
					'createdAt'	=> time(),
					) );
				$this->messenger->noteSuccess( $words->successAdded, $title );
				$this->restart( 'edit/'.$relocationId, TRUE );
			}
		}
		$data		= (object) array();
		foreach( $this->model->getColumns() as $column )
			$data->{$column}	= $this->request->get( $column );
		$this->addData( 'relocation', $data );
	}

	public function edit( $relocationId ){
		$relocation	= $this->checkRelocation( $relocationId );
		$words		= (object) $this->getWords( 'msg' );

		if( $this->request->has( 'save' ) ){
			$title	= $this->request->get( 'title' );
			$url	= $this->request->get( 'url' );
			if( $this->model->getAll( array( 'title' => $title, 'relocationId' => "!=".$relocationId ) ) ){
				$this->messenger->noteError( $words->errorTitleAlreadyExists, $title );
			}
			else if( $this->model->getAll( array( 'url' => $url, 'relocationId' => "!=".$relocationId ) ) ){
				$this->messenger->noteError( $words->errorUrlAlreadyExists, $url );
			}
			else{
				$this->model->edit( $relocationId, array(
					'status'	=> $this->request->get( 'status' ),
					'title'		=> $title,
					'url'		=> $url,
				) );
				$this->messenger->noteSuccess( $words->successEdited, $title );
				$this->restart( NULL, TRUE );
			}
		}

		if( $relocation->status < 0 )
			$this->messenger->noteNotice( $words->noteDeactivated );
		else if( $relocation->status < 1 )
			$this->messenger->noteNotice( $words->notePrepared );

		$this->addData( 'relocation', $relocation );
		$this->addData( 'shortcut', $this->shortcut );
	}

	public function filter( $reset = NULL ){
		$filterKeys	= array( 'id', 'status', 'title' );
		foreach( $filterKeys as $key ){
			if( $reset )
				$this->session->remove( $this->filterSessionPrefix.$key );
			if( $this->request->has( $key ) )
				$this->session->set( $this->filterSessionPrefix.$key, $this->request->get( $key ) );
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 ){
		$conditions		= array();
		$orders			= array();

		$filterId		= $this->session->get( $this->filterSessionPrefix.'id' );
		$filterStates	= $this->session->get( $this->filterSessionPrefix.'status' );
		$filterTitle	= $this->session->get( $this->filterSessionPrefix.'title' );
		if( $filterId )
			$conditions['relocationId']	= $filterId;
		else{
			if( $filterStates )
				$conditions['status']	= $filterStates;
			if( $filterTitle )
				$conditions['title']	= '%'.$filterTitle.'%';
		}

		$limit	= 10;
		$limits	= array( $page * $limit, $limit );

		$this->addData( 'limit', $limit );
		$this->addData( 'page', $page );
		$this->addData( 'total', $this->model->count() );
		$this->addData( 'count', $this->model->count( $conditions ) );
		$this->addData( 'relocations', $this->model->getAll( $conditions, $orders, $limits ) );
		$this->addData( 'filterId', $filterId );
		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterTitle', $filterTitle );
	}

	public function setStatus( $relocationId, $status ){
		$relocation	= $this->checkRelocation( $relocationId );
		$this->model->edit( $relocationId, array( 'status' => (int) $status ) );
		$this->restart( NULL, TRUE );
	}

	public function remove( $relocationId ){
		$relocation	= $this->checkRelocation( $relocationId );
		$this->model->remove( $relocationId );
		$this->messenger->noteSuccess( $words->successRemoved, $relocation->title );
		$this->restart( NULL, TRUE );
	}
}
