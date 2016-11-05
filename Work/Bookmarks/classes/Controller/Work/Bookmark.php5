<?php
#Fresh Moods - Orfine
#Rena Jones - Mesmerized
class Controller_Work_Bookmark extends CMF_Hydrogen_Controller{

	protected $useAuthentication	= FALSE;
	protected $userId				= 0;
	protected $request;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->model		= new Model_Bookmark( $this->env );
		$this->modelComment	= new Model_Bookmark_Comment( $this->env );
		$this->modelTag		= new Model_Bookmark_Tag( $this->env );
		if( $this->env->getModules()->has( 'Resource_Authentication' ) ){
			$this->useAuthentication	= TRUE;
			$this->userId	= (int) Logic_Authentication::getInstance()->getCurrentUserId();
		}
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			try{
				$pageHtml	= Net_Reader::readUrl( $this->request->get( 'url' ) );
				$data		= array(
					'userId'		=> $this->userId,
					'status'		=> 0,
					'url'			=> trim( $this->request->get( 'url' ) ),
					'title'			=> trim( $this->request->get( 'title' ) ),
					'pageContent'	=> trim( $pageHtml ),
					'createdAt'		=> time(),
				);
				$pageDocument	= new \PHPHtmlParser\Dom;
				$pageDocument->load( $pageHtml );

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

				$data['fulltext']	= join( ' ', array(
					$bookmark->title,
					$bookmark->pageTitle,
					$bookmark->url,
					$bookmark->pageDescription,
				) );

				$data['createdAt']	= time();
				$newId  = $this->model->add( $data );
				$this->restart( NULL, TRUE );
			}
			catch( Exception $e ){
				UI_HTML_Exception_Page::display( $e );
				exit;
//				throw new RuntimeException( 'Failed: '.$url, )
			}
		}
	}

	protected function check( $bookmarkId ){
		$bookmark	= $this->model->get( $bookmarkId );
		if( !$bookmark )
			throw new RangeException( 'Invalid bookmark ID' );
		return $bookmark;
	}

	public function comment( $bookmarkId ){
		$this->check( $bookmarkId );
		if( $this->request->has( 'save' ) ){
			$data		= array(
				'bookmarkId'	=> $bookmarkId,
				'userId'		=> $this->userId,
				'status'		=> 0,
				'content'		=> trim( $this->request->get( 'comment' ) ),
				'createdAt'		=> time(),
			);
			$newId  = $this->modelComment->add( $data );
			$this->restart( './view/'.$bookmarkId, TRUE );
		}
	}

	public function edit( $bookmarkId ){
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$data['modifiedAt']	= time();
			$newId  = $this->model->edit( $bookmarkId, $data );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'bookmark', $this->model->get( $bookmarkId ) );
	}

	public function filter( $reset = FALSE ){

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

	public function index( $page = 0 ){
		$filterQuery	= $this->session->get( 'filter_work_bookmark_query' );
		$conditions		= array(
			'userId'	=> $this->userId,
		);
		if( $filterQuery )
			$conditions['fulltext']	= '%'.$filterQuery.'%';
		$bookmarks		= $this->model->getAll( $conditions, array( 'createdAt' => 'DESC' ) );
		foreach( $bookmarks as $bookmark ){
			if( !$bookmark->fulltext ){
				$text	= join( ' ', array(
					$bookmark->title,
					$bookmark->pageTitle,
					$bookmark->url,
					$bookmark->pageDescription,
				) );
				$this->model->edit( $bookmark->bookmarkId, array( 'fulltext' => $text ) );
			}
			$bookmark->comments	= $this->modelComment->getAll( array(
				'bookmarkId'	=> $bookmarkId,
			), array( 'createdAt' => 'ASC' ) );
			$bookmark->tags	= $this->modelTag->getAll( array(
				'bookmarkId'	=> $bookmarkId,
			), array( 'title' => 'ASC' ) );
		}
		$this->addData( 'bookmarks', $bookmarks );
		$this->addData( 'filterQuery', $filterQuery );
		$this->addData( 'filterLimit', $this->session->get( 'filter_work_bookmark_limit' ) );
	}

	public function view( $bookmarkId ){
		$this->addData( 'bookmark', $this->check( $bookmarkId ) );
		$this->addData( 'comments', $this->modelComment->getAll( array(
			'bookmarkId'	=> $bookmarkId,
		), array( 'createdAt' => 'DESC' ) ) );
		$this->addData( 'tags', $this->modelTag->getAll( array(
			'bookmarkId'	=> $bookmarkId,
		), array( 'title' => 'ASC' ) ) );
	}

	public function visit( $bookmarkId ){
		$bookmark	= $this->check( $bookmarkId );
		$this->model->edit( $bookmarkId, array(
			'visits'	=> $bookmark->visits + 1,
			'visitedAt'	=> time(),
		) );
		header( 'Location: '.$bookmark->url );
		exit;
	}

	public function addTag( $bookmarkId ){
		$bookmark	= $this->check( $bookmarkId );
		if( $this->request->has( 'save' ) ){
			$this->modelTag->add( array(
				'bookmarkId'	=> $bookmarkId,
				'userId'		=> $this->userId,
				'title'			=> $this->request->get( 'tag' ),
				'createdAt'		=> time(),
				'relatedAt'		=> time(),
			) );
			$this->restart( './view/'.$bookmarkId, TRUE );
		}
	}
}
?>
