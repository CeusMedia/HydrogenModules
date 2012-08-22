<?php
class Controller_Blog extends CMF_Hydrogen_Controller{

	/**	@var	Model_Article		$model		Article model instance */
	protected $model;
	/**	@var	boolean				$isEditor	Indicates wheter user is editor */
	protected $isEditor;
	
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Environment object
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		parent::__construct( $env );
		$config			= $env->getConfig();
		$roleId			= $env->getSession()->get( 'roleId');
		$this->model	= new Model_Article( $env );
		$this->isEditor	= $roleId && $this->env->getAcl()->hasRight( $roleId, 'blog', 'add' );
	}

	/**
	 *	Adds an article, therefore receives title and content.
	 *	@access		public
	 *	@return		void
	 *	@throws		InvalidArgumentException	if title or content is missing
	 */
	public function add(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$userId		= $this->env->getSession()->get( 'userId' );
		if( $request->get( 'do' ) == 'save' ){
			if( !strlen( trim( $request->get( 'title' ) ) ) )
				$messenger->noteError( 'Der Titel des Artikels fehlt.' );
			if( !strlen( trim( $request->get( 'content' ) ) ) )
				$messenger->noteError( 'Der Inhalt des Artikels fehlt.' );
			if( !$messenger->gotError() ){
				$data	= array(
					'title'			=> trim( $request->get( 'title' ) ),
					'content'		=> trim( $request->get( 'content' ) ),
					'createdAt'		=> time(),
					'status'		=> 0,
				);
				$model	= new Model_Article( $this->env );
				$articleId	= $model->add( $data, FALSE );
				if( !$articleId ){
					$this->env->getMessenger()->noteError( 'Der Artikel konnte nicht gespeichert werden.' );
					return;
				}
				$model	= new Model_ArticleAuthor( $this->env );
				$model->add( array( 'articleId' => $articleId, 'userId' => $userId ) );
				$this->env->getMessenger()->noteSuccess( 'Der Artikel wurde gespeichert.' );
				$this->restart( './blog/edit/'.$articleId );
			}
		}
	}

	public function addAuthor( $articleId, $userId ){
		$request		= $this->env->getRequest();
		$modelRelation	= new Model_ArticleAuthor( $this->env );
		$indices		= array( 'articleId' => $articleId, 'userId' => $userId );
		if( !$modelRelation->getByIndices( $indices ) )
			$modelRelation->add( $indices );
		$this->restart( './blog/edit/'.$articleId );
	}

	public function addTag( $articleId ){
		$request		= $this->env->getRequest();
		$modelTag		= new Model_Tag( $this->env );
		$modelRelation	= new Model_ArticleTag( $this->env );

		if( (int) $articleId < 1 )
			$this->restart( './blog' );

		$tagName	= $request->get( 'tag' );
		if( !strlen( $tagName ) )
			$this->restart( './blog/edit/'.$articleId );
		
		$tags	= explode( ' ', str_replace( ',', ' ', $tagName ) );
		foreach( $tags as $tagName ){
			$tag		= $modelTag->getByIndex( 'title', trim( $tagName ) );
			if( $tag ){
				$tagId	= $tag->tagId;
				$number	= $tag->number;
				$indices	= array(
					'articleId'	=> $articleId,
					'tagId'		=> $tagId,
				);
				if( $modelRelation->getByIndices( $indices ) )
					$this->restart( './blog/edit/'.$articleId );
			}
			else{
				$tagId	= $modelTag->add( array( 'title' => trim( $tagName ) ) );
				$number	= 0;
			}
			$data	= array(
				'articleId'	=> $articleId,
				'tagId'		=> $tagId,
			);
			$modelRelation->add( $data );
			$modelTag->edit( $tagId, array( 'number' => ++$number ) );
		}
		$this->restart( './blog/edit/'.$articleId );		

	}

	public function article( $articleId, $version = 0, $title = NULL ){
		$articleId	= preg_replace( "/^(\d+).*/", '\\1', trim( $articleId ) );
		if( (int) $articleId < 1 )
			$this->restart( './blog' );

		$version	= preg_replace( "/^(\d+).*/", '\\1', trim( $version ) );

		$states		= $this->env->getSession()->get( 'filter_blog_states' );
		if( !$this->isEditor )
			$states	= array( 1 );
		else if( !$states )
			$this->env->getSession()->set( 'filter_blog_states', $states = array( 0, 1 ) );
		$conditions	= array( 'status' => $states );

		$article				= $this->model->get( $articleId );
		$article->versions		= $this->model->getArticleVersions( $articleId );
		$article->version		= count( $article->versions ) + 1;
		if( $version < 1 )
			$version	= $article->version;

		$data	= array(
			'articles'	=> $this->model->getAll( $conditions, array( 'createdAt' => 'DESC' ) ),
			'article'	=> $article,
			'tags'		=> $this->model->getArticleTags( $articleId ),
			'authors'	=> $this->model->getArticleAuthors( $articleId ),
			'articleId'	=> rawurldecode( $articleId ),
			'version'	=> $version,
			'config'	=> new ADT_List_Dictionary( $this->env->getConfig()->getAll( 'module.blog_compact.' ) )
		);
		$this->setData( $data );
	}

	public function author( $username = NULL ){
		$model	= new Model_User( $this->env );
		$user	= $model->getByIndex( 'username', $username );
		if( !$user ){
			$this->env->getMessenger()->noteError( 'Invalid user' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'user', $user );
	}

	public function dev( $topic = NULL ){
		$topic		= strlen( $topic ) ? $topic : NULL;
		$fileName	= 'contents/dev_'.$topic.'.txt';
		$this->addData( 'dev', '' );
		if( file_exists( $fileName ) )
			$this->addData( 'dev', File_Reader::load( $fileName ) );
	}

	public function edit( $articleId, $version = 0 ){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();

		if( (int) $articleId < 1 )
			$this->restart( './blog' );
		$article	= $this->model->get( $articleId );
		if( !$article )
			$this->restart( './blog' );
		
		$model		= new Model_ArticleVersion( $this->env );
		$article->versions	= $this->model->getArticleVersions( $articleId );
		$article->version	= count( $article->versions ) + 1;
		if( $version < 1 )
			$version	= $article->version;

		$userId		= $this->env->getSession()->get( 'userId' );

		if( $request->get( 'save' ) ){
			if( !strlen( trim( $request->get( 'title' ) ) ) )
				throw new InvalidArgumentException( 'Article title is missing' ) ;
			if( !strlen( trim( $request->get( 'content' ) ) ) )
				throw new InvalidArgumentException( 'Article content is missing' ) ;
	
			$createdAt	= strtotime( $request->get( 'date' ).' '.$request->get( 'time' ).':00' );	//  new creation date string
			$createdAt	= ( $createdAt && !$request->get( 'now' ) ) ? $createdAt : time();

			$data		= array(
				'title'		=> trim( $request->get( 'title' ) ),
				'content'	=> trim( $request->get( 'content' ) ),
				'status'	=> $request->get( 'status' ),
				'createdAt'	=> $createdAt,
			);
			
			$model	= new Model_Article( $this->env );
			if( !$model->edit( $articleId, $data, FALSE ) ){
				$messenger->noteError( 'Am Artikel wurde nichts geändert.' );
				return;
			}
			$model->edit( $articleId, array( 'modifiedAt' => time() ) );
			$this->env->getMessenger()->noteSuccess( 'Der Artikel wurde geändert.' );
			
			if( $request->get( 'version' ) ){
				$model	= new Model_ArticleVersion( $this->env );
				$data	= array(
					'articleId'		=> $article->articleId,
					'title'			=> $article->title,
					'content'		=> $article->content,
					'createdAt'		=> $article->createdAt,
					'modifiedAt'	=> max( $article->createdAt, $article->modifiedAt ),
				);
				$model->add( $data, FALSE );
				$messenger->noteNotice( 'Die vorherige Version des Artikels wurde archiviert.' );
			}
			
			$model	= new Model_ArticleAuthor( $this->env );
			if( !$model->count( array( 'articleId' => $articleId, 'userId' => $userId ) ) )
				$model->add( array( 'articleId' => $articleId, 'userId' => $userId ) );

			
			$this->restart( './blog/edit/'.$articleId );
		}
		$modelUser	= new Model_User( $this->env );
		$data	= array(
			'articles'	=> $this->model->getAll(),
			'article'	=> $this->model->get( $articleId ),
			'tags'		=> $this->model->getArticleTags( $articleId ),
			'authors'	=> $this->model->getArticleAuthors( $articleId ),
			'editors'	=> $modelUser->getAll( array( 'status' => '>0' ) ),
			'articleId'	=> rawurldecode( $articleId ),
			'tags'		=> $this->model->getArticleTags( $articleId ),
			'version'	=> $version,
		);
		$this->setData( $data );
	}
	
	/**
	 *	Generates RSS Feed and returns it directly to the requesting client.
	 *	@access		public
	 *	@param		integer		$limit
	 *	@param		boolean		$debug
	 *	@return		void
	 */
	public function feed( $limit = 10, $debug = NULL ){
		$limit		= ( (int) $limit > 0 ) ? (int) $limit : 10;
		
		$conditions	= array( 'status' => 1 );
		$orders		= array( 'articleId' => 'DESC' );
		$articles	= $this->model->getAll( $conditions, $orders, array( 0, $limit ) );
		$this->addData( 'articles', $articles );
		$this->addData( 'debug', (bool) $debug );
	}
	
	protected function getFilteredStates(){
		$states	= $this->env->getSession()->get( 'filter_blog_states' );
		if( !is_array( $states ) )
			$this->env->getSession()->set( 'filter_blog_states', $states = array( 1 ) );
		if( !$this->isEditor /*|| !$states*/ )
			$this->env->getSession()->set( 'filter_blog_states', $states = array( 1 ) );
		return $states;
	}

	public function index( $page = 0, $limit = NULL ){
		$perPage	= abs( (int) $this->env->getConfig()->get( 'module.blog_compact.perPage' ) );
		$states		= $this->getFilteredStates();
		
		$limit		= !is_null( $limit ) ? $limit : ( $perPage ? $perPage : 10 );
		$offset		= $page * $limit;
		$limits		= array( $offset, $limit );
		$conditions	= array( 'status' => $states ? $states : -99 );
		$orders		= array( 'createdAt' => 'DESC'/*, 'articleId' => 'DESC'*/ );
		$articles	= $this->model->getAll( $conditions, $orders, $limits );

		foreach( $articles as $nr => $article ){
			$article->authors		= $this->model->getArticleAuthors( $article->articleId );
			$article->tags			= $this->model->getArticleTags( $article->articleId );
			$article->versions		= $this->model->getArticleVersions( $article->articleId );
			$article->version		= count( $article->versions ) + 1;
		}
		
		$query		= 'SELECT COUNT(at.articleId) as nr, at.tagId, t.title FROM articles AS a, article_tags AS at, tags AS t WHERE at.tagId=t.tagId AND at.articleId=a.articleId AND a.status=1 GROUP BY at.tagId ORDER BY nr DESC LIMIT 0, 10';
		$topTags	= $this->env->getDatabase()->query( $query )->fetchAll( PDO::FETCH_OBJ );
		$data		= array(
			'page'		=> $page,
			'limit'		=> $limit,
			'offset'	=> $offset,
			'articles'	=> $articles,
			'states'	=> $states,
			'number'	=> $this->model->count( $conditions ),
			'topTags'	=> $topTags,
			'isEditor'	=> $this->isEditor,
			'config'	=> new ADT_List_Dictionary( $this->env->getConfig()->getAll( 'module.blog_compact.' ) )
		);
		$this->setData( $data );
	}

	public function removeAuthor( $articleId, $userId ){
		$model		= new Model_ArticleAuthor( $this->env );
		$model->removeByIndices( array( 'articleId' => $articleId, 'userId' => $userId ) );
		$this->restart( './blog/edit/'.$articleId );
	}

	public function removeTag( $articleId, $tagId ){
		$model	= new Model_ArticleTag( $this->env );
		$indices	= array(
			'articleId'	=> $articleId,
			'tagId'		=> $tagId,
		);
		if( $model->removeByIndices( $indices ) ){
			$model	= new Model_Tag( $this->env );
			$tag	= $model->get( $tagId );
			$number	= $tag->number--;
			$model->edit( $articleId, array( 'number' => $number ) );
		}
		$this->restart( './blog/edit/'.$articleId );
	}

	public function setContent( $articleId ){
		$request	= $this->env->getRequest();
		if( (int) $articleId < 1 || !( $article	= $this->model->get( (int) $articleId ) ) )
			$this->restart( './blog' );

		if( $article->content != $request->get( 'content' ) ){
			$data	= array(
				'content'		=> $request->get( 'content' ),
				'modifiedAt'	=> time(),
			);
			$this->model->edit( $articleId, $data, FALSE );
		}
		$request->isAjax() ? exit : $this->restart( './blog/edit/'.$articleId );
	}

	public function setFilter(){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$mode		= $request->get( 'mode' );
		$name		= $request->get( 'name' );
		$value		= $request->get( 'value' );
		$store		= $session->get( 'filter_blog_'.$name );
#		$session->remove( 'filter_blog_states' );
		switch( $mode ){
			case 'add':
				if( !is_array( $store ) )
					$store	= array();
				$store[]	= $value;
				$session->set( 'filter_blog_'.$name, $store );
				break;
			case 'set':
				$session->set( 'filter_blog_'.$name, $value );
				break;
			case 'remove':
				if( !is_array( $store ) )
					$session->remove( 'filter_blog_'.$name );
				else{
					$store	= array_diff( $store, array( $value ) );
					$session->set( 'filter_blog_'.$name, $store );
				}
				break;
		}
		if( $request->isAjax() )
			exit;
		$this->restart( './blog' );
	}

	public function setStatus( $articleId, $status ){
		$words	= $this->getWords( 'states' );
		if( (int) $articleId < 1 )
			$this->restart( './blog' );
		$article	= $this->model->get( $articleId );
		if( !$article )
			$this->restart( './blog' );
		$this->model->edit( $articleId, array( 'status' => $status ) );
		$this->env->getMessenger()->noteSuccess( 'Der Status wurde auf <cite>'.$words[$status].'</cite> gesetzt.' );
		$this->restart( './blog/edit/'.$articleId );
	}
	
	public function tag( $tagName ){
		$model		= new Model_Tag( $this->env );
		$tag		= $model->getByIndex( 'title', $tagName );
		$states		= $this->getFilteredStates();
		$articles		= array();
		$relatedTags	= array();
		if( $tag ){
			$model		= new Model_ArticleTag( $this->env );
			$relations	= $model->getAllByIndex( 'tagId', $tag->tagId );
			$model		= new Model_Article( $this->env );
			foreach( $relations as $relation ){
				$article	= $model->get( $relation->articleId );
				if( in_array( $article->status, $states ) )
					$articles[$article->articleId]	= $article;
			}
			krsort( $articles );
			
			$model			= new Model_Tag( $this->env );
			$relatedTags	= $model->getRelatedTags( $tag->tagId );
		}
		foreach( $articles as $nr => $article ){
			$articles[$nr]->authors	= $this->model->getArticleAuthors( $article->articleId );
			$articles[$nr]->tags	= $this->model->getArticleTags( $article->articleId );
		}
		$this->setData( array(
			'articles'	=> $articles,
			'tag'		=> $tag,
			'tagName'	=> $tagName,
			'friends'	=> $relatedTags
		) );
	}

	public function thumb( $file ){
		$config	= $this->env->getConfig();
		$path	= $config->get( 'path.images' ).$config->get( 'module.blog_compact.path.images' );
		$this->addData( 'path', $path );
		$this->addData( 'file', $file );
	}
}
?>