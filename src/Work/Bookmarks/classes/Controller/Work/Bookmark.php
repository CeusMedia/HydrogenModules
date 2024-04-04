<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\Reader as NetReader;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Bookmark extends Controller
{
	protected bool $useAuthentication	= FALSE;
	protected ?string $userId			= NULL;
	protected Dictionary $request;
	protected Dictionary $session;
	protected Model_Bookmark $model;
	protected Model_Bookmark_Comment $modelComment;
	protected Model_Bookmark_Tag $modelTag;

	public function add()
	{
		if( $this->request->has( 'save' ) ){
			try{
				$pageHtml	= NetReader::readUrl( $this->request->get( 'url' ) );
				$data		= [
					'userId'		=> $this->userId,
					'status'		=> 0,
					'url'			=> trim( $this->request->get( 'url' ) ),
					'title'			=> trim( $this->request->get( 'title' ) ),
					'pageContent'	=> trim( $pageHtml ),
					'createdAt'		=> time(),
				];
				$pageDocument	= new \PHPHtmlParser\Dom;
				$pageDocument->loadStr( $pageHtml );

				$pageTitle = $pageDocument->find( 'title' );
				if( $pageTitle->count() ){
					$data['pageTitle']	= $pageTitle[$pageTitle->count()-1]->text;
					if( !count( $data['title'] ) )
						$data['title']	= $data['pageTitle'];
				}
				foreach( $pageDocument->find( 'meta' ) as $meta ){
					if( strtolower( $meta->getAttribute( 'name' ) ) === 'description' )
						$data['pageDescription']	= $meta->getAttribute( 'content' );
				}

				$data['createdAt']	= time();
				$bookmarkId  = $this->model->add( $data );
				$bookmark	= $this->model->get( $bookmarkId );
				$data['fulltext']	= join( ' ', [
					$bookmark->title,
					$bookmark->pageTitle,
					$bookmark->url,
					$bookmark->pageDescription,
				] );
				$this->model->edit( $bookmarkId, $data );

				$this->restart( NULL, TRUE );
			}
			catch( Exception $e ){
				HtmlExceptionPage::display( $e );
				exit;
//				throw new RuntimeException( 'Failed: '.$url, )
			}
		}
	}

	public function comment( string $bookmarkId ): void
	{
		$this->check( $bookmarkId );
		if( $this->request->has( 'save' ) ){
			$data		= [
				'bookmarkId'	=> $bookmarkId,
				'userId'		=> $this->userId,
				'status'		=> 0,
				'content'		=> trim( $this->request->get( 'comment' ) ),
				'createdAt'		=> time(),
			];
			$newId  = $this->modelComment->add( $data );
			$this->restart( './view/'.$bookmarkId, TRUE );
		}
	}

	public function edit( string $bookmarkId ): void
	{
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$data['modifiedAt']	= time();
			$this->model->edit( $bookmarkId, $data );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'bookmark', $this->model->get( $bookmarkId ) );
	}

	public function filter( $reset = FALSE ): void
	{
		if( $reset ){
			foreach( array_keys( $this->session->getAll( 'filter_work_bookmark_' ) ) as $key )
				$this->session->remove( 'filter_work_bookmark_'.$key );
		}
		else{
			$this->session->set( 'filter_work_bookmark_limit', $this->request->get( 'limit' ) );
			$this->session->set( 'filter_work_bookmark_query', $this->request->get( 'query' ) );
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 ): void
	{
		$filterQuery	= $this->session->get( 'filter_work_bookmark_query' );
		$conditions		= [
			'userId'	=> $this->userId,
		];
		if( $filterQuery )
			$conditions['fulltext']	= '%'.$filterQuery.'%';
		$bookmarks		= $this->model->getAll( $conditions, ['createdAt' => 'DESC'] );
		foreach( $bookmarks as $bookmark ){
			if( !$bookmark->fulltext ){
				$text	= join( ' ', [
					$bookmark->title,
					$bookmark->pageTitle,
					$bookmark->url,
					$bookmark->pageDescription,
				] );
				$this->model->edit( $bookmark->bookmarkId, ['fulltext' => $text] );
			}
			$bookmark->comments	= $this->modelComment->getAll( [
				'bookmarkId'	=> $bookmark->bookmarkId,
			], ['createdAt' => 'ASC'] );
			$bookmark->tags	= $this->modelTag->getAll( [
				'bookmarkId'	=> $bookmark->bookmarkId,
			], ['title' => 'ASC'] );
		}
		$this->addData( 'bookmarks', $bookmarks );
		$this->addData( 'filterQuery', $filterQuery );
		$this->addData( 'filterLimit', $this->session->get( 'filter_work_bookmark_limit' ) );
	}

	public function view( string $bookmarkId ): void
	{
		$this->addData( 'bookmark', $this->check( $bookmarkId ) );
		$this->addData( 'comments', $this->modelComment->getAll( [
			'bookmarkId'	=> $bookmarkId,
		], ['createdAt' => 'DESC'] ) );
		$this->addData( 'tags', $this->modelTag->getAll( [
			'bookmarkId'	=> $bookmarkId,
		], ['title' => 'ASC'] ) );
	}

	public function visit( string $bookmarkId ): void
	{
		$bookmark	= $this->check( $bookmarkId );
		$this->model->edit( $bookmarkId, [
			'visits'	=> $bookmark->visits + 1,
			'visitedAt'	=> time(),
		] );
		header( 'Location: '.$bookmark->url );
		exit;
	}

	public function addTag( string $bookmarkId ): void
	{
		$bookmark	= $this->check( $bookmarkId );
		if( $this->request->has( 'save' ) ){
			$this->modelTag->add( [
				'bookmarkId'	=> $bookmarkId,
				'userId'		=> $this->userId,
				'title'			=> $this->request->get( 'tag' ),
				'createdAt'		=> time(),
				'relatedAt'		=> time(),
			] );
			$this->restart( './view/'.$bookmarkId, TRUE );
		}
	}

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->model		= new Model_Bookmark( $this->env );
		$this->modelComment	= new Model_Bookmark_Comment( $this->env );
		$this->modelTag		= new Model_Bookmark_Tag( $this->env );
		if( $this->env->getModules()->has( 'Resource_Authentication' ) ){
			$this->useAuthentication	= TRUE;
			$this->userId	= (int) Logic_Authentication::getInstance( $this->env )->getCurrentUserId();
		}
	}

	protected function check( string $bookmarkId ): object
	{
		$bookmark	= $this->model->get( $bookmarkId );
		if( !$bookmark )
			throw new RangeException( 'Invalid bookmark ID' );
		return $bookmark;
	}
}
