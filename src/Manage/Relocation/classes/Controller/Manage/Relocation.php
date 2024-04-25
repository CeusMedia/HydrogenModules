<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Manage_Relocation extends Controller
{
	protected Model_Relocation $model;
	protected Messenger $messenger;
	protected Request $request;
	protected Dictionary $session;
	protected string $filterSessionPrefix		= 'filter-manage-relocation-';
	protected ?string $shortcut					= NULL;

	public function add(): void
	{
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
		$data		= (object) [];
		foreach( $this->model->getColumns() as $column )
			$data->{$column}	= $this->request->get( $column );
		$this->addData( 'relocation', $data );
	}

	public function edit( string $relocationId ): void
	{
		$relocation	= $this->checkRelocation( $relocationId );
		$words		= (object) $this->getWords( 'msg' );

		if( $this->request->has( 'save' ) ){
			$title	= $this->request->get( 'title' );
			$url	= $this->request->get( 'url' );
			if( $this->model->getAll( ['title' => $title, 'relocationId' => "!= ".$relocationId] ) ){
				$this->messenger->noteError( $words->errorTitleAlreadyExists, $title );
			}
			else if( $this->model->getAll( ['url' => $url, 'relocationId' => "!= ".$relocationId] ) ){
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


	public function export( $page = 0 ): void
	{
		$conditions		= [];
		$filterId		= $this->session->get( $this->filterSessionPrefix.'id' );
		$filterStatus	= $this->session->get( $this->filterSessionPrefix.'status' );
		$filterTitle	= $this->session->get( $this->filterSessionPrefix.'title' );
		$filterOrderCol	= $this->session->get( $this->filterSessionPrefix.'orderColumn' );
		$filterOrderDir	= $this->session->get( $this->filterSessionPrefix.'orderDirection' );
		if( $filterId )
			$conditions['relocationId']	= $filterId;
		else{
			if( $filterStatus )
				$conditions['status']	= $filterStatus;
			if( $filterTitle )
				$conditions['title']	= '%'.$filterTitle.'%';
		}

		$orders			= [];
		$allowedColumns	= array( 'relocationId', 'title', 'views', 'usedAt' );
		if( !in_array( $filterOrderCol, $allowedColumns ) )
			$filterOrderCol	= 'relocationId';
		if( !in_array( $filterOrderDir, array( 'asc', 'desc' ) ) )
			$filterOrderDir	= 'asc';
		$orders[$filterOrderCol]	= $filterOrderDir;

		$data	= $this->model->getAll( $conditions, $orders );
		$states	= $this->getWords( 'states' );
		$keys	= ['relocationId' => 'ID', 'status' => 'Zustand', 'views' => 'Klicks', 'usedAt' => 'zuletzt', 'title' => 'Titel', 'url' => 'Zieladresse'];
		$lines  = [join( ';', $keys )];
		$helper	= new View_Helper_TimePhraser( $this->env );
		foreach( $data as $line ){
			$row	= [];
			foreach( array_keys( $keys ) as $key ){
				$value	= $line->$key;
				switch( $key ){
					case 'status':
						$value	= $states[$value];
						break;
					case 'usedAt':
						$value	= date( 'Y-m-d', $value );
						break;
//					case 'usedAt':
//						$value	= $helper->convert( $value, FALSE, 'vor' );
//						break;
				}
				$row[]	= '"'.addslashes( $value ).'"';
			}
			$lines[]    = join( ';', $row );
		}
		$csv	= join( "\r\n", $lines );

		$fileName	= 'Export_'.date( 'Y-m-d_H:i:s' ).'.csv';
		HttpDownload::sendString( $csv, $fileName, TRUE );

		$this->redirect( NULL, TRUE );
	}


	public function filter( $reset = NULL ): void
	{
		$filterKeys	= ['id', 'status', 'title', 'orderColumn', 'orderDirection'];
		foreach( $filterKeys as $key ){
			if( $reset )
				$this->session->remove( $this->filterSessionPrefix.$key );
			if( $this->request->has( $key ) )
				$this->session->set( $this->filterSessionPrefix.$key, $this->request->get( $key ) );
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 ): void
	{
		$conditions		= [];
		$filterId		= $this->session->get( $this->filterSessionPrefix.'id' );
		$filterStatus	= $this->session->get( $this->filterSessionPrefix.'status' );
		$filterTitle	= $this->session->get( $this->filterSessionPrefix.'title' );
		$filterOrderCol	= $this->session->get( $this->filterSessionPrefix.'orderColumn' );
		$filterOrderDir	= $this->session->get( $this->filterSessionPrefix.'orderDirection' );
		if( $filterId )
			$conditions['relocationId']	= $filterId;
		else{
			if( $filterStatus )
				$conditions['status']	= $filterStatus;
			if( $filterTitle )
				$conditions['title']	= '%'.$filterTitle.'%';
		}

		$orders			= [];
		$allowedColumns	= ['relocationId', 'title', 'views', 'usedAt'];
		if( !in_array( $filterOrderCol, $allowedColumns ) )
			$filterOrderCol	= 'relocationId';
		if( !in_array( $filterOrderDir, ['asc', 'desc'] ) )
			$filterOrderDir	= 'asc';
		$orders[$filterOrderCol]	= $filterOrderDir;

		$limit	= 10;
		$limits	= [$page * $limit, $limit];

		$this->addData( 'limit', $limit );
		$this->addData( 'page', $page );
		$this->addData( 'total', $this->model->count() );
		$this->addData( 'count', $this->model->count( $conditions ) );
		$this->addData( 'relocations', $this->model->getAll( $conditions, $orders, $limits ) );
		$this->addData( 'filterId', $filterId );
		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterTitle', $filterTitle );
		$this->addData( 'filterOrderColumn', $filterOrderCol );
		$this->addData( 'filterOrderDirection', $filterOrderDir );
	}

	public function setStatus( string $relocationId, $status ): void
	{
		$relocation	= $this->checkRelocation( $relocationId );
		$this->model->edit( $relocationId, array( 'status' => (int) $status ) );
		$this->restart( NULL, TRUE );
	}

	public function remove( string $relocationId ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		$relocation	= $this->checkRelocation( $relocationId );
		$this->model->remove( $relocationId );
		$this->messenger->noteSuccess( $words->successRemoved, $relocation->title );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit(): void
	{
		$this->model		= new Model_Relocation( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();

		$frontend	= Logic_Frontend::getInstance( $this->env );
		$moduleConfig	= $frontend->getModuleConfigValues( 'Info_Relocation' );

		if( $moduleConfig['shortcut'] )
			$this->shortcut		= preg_replace( "/^[^a-z]+([a-z]+)[^a-z]+$/", "\\1", $moduleConfig['shortcut.source'] );
	}

	protected function checkRelocation( string $relocationId ): object
	{
		$words		= (object) $this->getWords( 'msg' );
		$relocation	= $this->model->get( $relocationId );
		if( !$relocation ){
			$this->messenger->noteError( $words->errorIdInvalid );
			$this->restart( NULL, TRUE );
		}
		return $relocation;
	}
}
