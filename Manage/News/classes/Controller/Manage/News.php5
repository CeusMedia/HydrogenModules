<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_News extends Controller{

	protected function __onInit(){
		$this->model		= new Model_News( $this->env );
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
	}

	public function add(){
		$words	= $this->getWords();
		if( $this->request->has( 'save' ) ){
			$data	= array(
				'status'		=> (int) $this->request->get( 'status' ),
				'title'			=> $this->request->get( 'title' ),
				'content'		=> $this->request->get( 'content' ),
				'columns'		=> 1,//$this->request->get( 'columns' ),
				'createdAt'		=> time(),
			);
			if( $this->request->get( 'startsAt' ) && @strtotime( $this->request->get( 'startsAt' ) ) )
				$data['startsAt']	= strtotime( $this->request->get( 'startsAt' ) );
			if( $this->request->get( 'endsAt' ) && @strtotime( $this->request->get( 'endsAt' ) ) )
				$data['endsAt']		= strtotime( $this->request->get( 'endsAt' ) );
			$newsId	= $this->model->add( $data, FALSE );
			$this->messenger->noteSuccess( $words['msg']['successAdded'] );
			$this->restart( 'manage/news/edit/'.$newsId );
		}
		$news	= (object) array(
			'status'		=> (int) $this->request->get( 'status' ),
			'title'			=> $this->request->get( 'title' ),
			'content'		=> $this->request->get( 'content' ),
			'columns'		=> 1,//$this->request->get( 'columns' ),
			'startsAt'		=> $this->request->get( 'startsAt' ),
			'endsAt'		=> $this->request->get( 'endsAt' ),
		);
		$this->addData( 'news', $news, FALSE );
	}

	public function edit( $newsId ){
		$words	= $this->getWords();
		if( !( strlen( trim( $newsId ) ) && (int) $newsId ) )
			throw new OutOfRangeException( 'No news ID given' );
		$news	= $this->model->get( (int) $newsId );
		if( !$news )
			throw new OutOfRangeException( 'Invalid news ID given' );

		if( $this->request->has( 'save' ) ){
			$data	= array(
				'status'		=> (int) $this->request->get( 'status' ),
				'title'			=> $this->request->get( 'title' ),
				'content'		=> $this->request->get( 'content' ),
				'columns'		=> 1,//$this->request->get( 'columns' ),
			);
			if( $this->request->get( 'startsAt' ) && @strtotime( $this->request->get( 'startsAt' ) ) )
				$data['startsAt']	= strtotime( $this->request->get( 'startsAt' ) );
			if( $this->request->get( 'endsAt' ) && @strtotime( $this->request->get( 'endsAt' ) ) )
				$data['endsAt']		= strtotime( $this->request->get( 'endsAt' ) );
			$this->model->edit( $newsId, $data, FALSE );
			$this->messenger->noteSuccess( $words['msg']['successModified'] );
			$this->restart( NULL, TRUE );
		}
//		$news->startsAt	= $news->startsAt ? date( 'Y-m-d', $news->startsAt ) : $news->startsAt;
//		$news->endsAt	= $news->endsAt ? date( 'Y-m-d', $news->endsAt ) : $news->endsAt;

		$this->addData( 'news', $news );
		$this->addData( 'newsId', $newsId );
	}

	public function filter( $reset = NULL ){
		$prefix	= 'filter_manage_news_';
		if( $reset ){
			$this->session->remove( $prefix.'query' );
			$this->session->remove( $prefix.'status' );
		}
		$this->session->set( $prefix.'query', $this->request->get( 'query' ) );
		$this->session->set( $prefix.'status', $this->request->get( 'status' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $pageNr = 0, $limit = 15 ){
		$limit		= max( 10, min( 100, abs( $limit ) ) );
		$filterQuery	= $this->session->get( 'filter_manage_news_query' );
		$filterStatus	= $this->session->get( 'filter_manage_news_status' );

		$conditions	= [];
		if( strlen( trim( $filterQuery ) ) )
			$conditions['title']	= '%'.str_replace( ' ', '%', $filterQuery );
		if( strlen( $filterStatus ) )
			$conditions['status']	= $filterStatus;

		$orders		= ['newsId' => 'DESC'];
		$limits		= [$pageNr * $limit, $limit];
		$this->addData( 'pageNr', $pageNr );
		$this->addData( 'limit', $limit );
		$this->addData( 'total', $this->model->count( $conditions ) );
		$this->addData( 'news', $this->model->getAll( $conditions, $orders, $limits ) );

		$this->addData( 'filterQuery', $filterQuery );
		$this->addData( 'filterStatus', $filterStatus );
	}

	public function remove( $newsId ){
		$words	= $this->getWords();
		$news	= $this->model->get( $newsId );
		if( $news ){
			$this->model->remove( $newsId );
			$this->messenger->noteSuccess( $words['msg']['successRemoved'], htmlentities( $news->title, ENT_QUOTES, 'UTF-8' ) );
		}
		$this->restart( NULL, TRUE );
	}
}
?>
