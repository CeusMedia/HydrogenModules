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

	public function addTag( $articleId ){
		$request		= $this->env->getRequest();
		$modelTag		= new Model_Tag( $this->env );
		$modelRelation	= new Model_ArticleTag( $this->env );

		if( (int) $articleId < 1 )
			$this->restart( './blog/' );

		$tagName	= $request->get( 'tag' );
		if( !strlen( $tagName ) )
			$this->restart( './blog/edit/'.$articleId );
		$tag		= $modelTag->getByIndex( 'title', $tagName );
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
			$tagId	= $modelTag->add( array( 'title' => $tagName ) );
			$number	= 0;
		}
		$data	= array(
			'articleId'	=> $articleId,
			'tagId'		=> $tagId,
		);
		$modelRelation->add( $data );
		$modelTag->edit( $tagId, array( 'number' => ++$number ) );
		$this->restart( './blog/edit/'.$articleId );
	}

	public function article( $articleId, $title = NULL ){
		$articleId	= preg_replace( "/^(\d+).*/", '\\1', trim( $articleId ) );
		if( (int) $articleId < 1 )
			$this->restart( './blog/' );

		$states		= $this->env->getSession()->get( 'filter_blog_states' );
		if( !$this->isEditor )
			$states	= array( 1 );
		else if( !$states )
			$this->env->getSession()->set( 'filter_blog_states', $states = array( 0, 1 ) );
		$conditions	= array( 'status' => $states );
		
		$data	= array(
			'articles'	=> $this->model->getAll( $conditions ),
			'article'	=> $this->model->get( $articleId ),
			'tags'		=> $this->model->getArticleTags( $articleId ),
			'authors'	=> $this->model->getArticleAuthors( $articleId ),
			'articleId'	=> rawurldecode( $articleId ),
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

	public function edit( $articleId ){
		if( (int) $articleId < 1 )
			$this->restart( './blog/' );
		$request	= $this->env->getRequest();
		$userId		= $this->env->getSession()->get( 'userId' );
		
		if( $request->get( 'do' ) == 'save' ){
			if( !strlen( trim( $request->get( 'title' ) ) ) )
				throw new InvalidArgumentException( 'Article title is missing' ) ;
			if( !strlen( trim( $request->get( 'content' ) ) ) )
				throw new InvalidArgumentException( 'Article content is missing' ) ;
			
			$data	= array(
				'title'			=> trim( $request->get( 'title' ) ),
				'content'		=> trim( $request->get( 'content' ) ),
				'status'		=> $request->get( 'status' ),
			);
			$model	= new Model_Article( $this->env );
			if( !$model->edit( $articleId, $data, FALSE ) ){
				$this->env->getMessenger()->noteError( 'Am Artikel wurde nichts geändert.' );
				return;
			}
			$model->edit( $articleId, array( 'modifiedAt' => time() ) );
			
			$model	= new Model_ArticleAuthor( $this->env );
			if( !$model->count( array( 'articleId' => $articleId, 'userId' => $userId ) ) )
				$model->add( array( 'articleId' => $articleId, 'userId' => $userId ) );

			
			$this->env->getMessenger()->noteSuccess( 'Der Artikel wurde geändert.' );
		}
		
		$data	= array(
			'articles'	=> $this->model->getAll(),
			'article'	=> $this->model->get( $articleId ),
			'tags'		=> $this->model->getArticleTags( $articleId ),
			'authors'	=> $this->model->getArticleAuthors( $articleId ),
			'articleId'	=> rawurldecode( $articleId ),
			'tags'		=> $this->model->getArticleTags( $articleId ),
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
		$articles	= $this->model->getAll( $conditions, $orders, array( $limit, 0 ) );
		$this->addData( 'articles', $articles );
		$this->addData( 'debug', (bool) $debug );
	}

	public function index( $page = 0, $limit = NULL ){
		$perPage	= abs( (int) $this->env->getConfig()->get( 'module.blog_compact.perPage' ) );
		$states		= $this->env->getSession()->get( 'filter_blog_states' );
		if( !$this->isEditor )
			$states	= array( 1 );
		else if( !$states )
			$this->env->getSession()->set( 'filter_blog_states', $states = array( 0, 1 ) );
		
		$limit		= !is_null( $limit ) ? $limit : ( $perPage ? $perPage : 10 );
		$offset		= $page * $limit;
		$limits		= array( $offset, $limit );
		$conditions	= array( 'status' => $states );
		$orders		= array( 'articleId' => 'DESC' );
		$articles	= $this->model->getAll( $conditions, $orders, $limits );
#		remark( $this->model->getLastQuery() );
#		die;
		foreach( $articles as $nr => $article ){
			$articles[$nr]->authors	= $this->model->getArticleAuthors( $article->articleId );
			$articles[$nr]->tags	= $this->model->getArticleTags( $article->articleId );
		}
		
		$query		= 'SELECT COUNT(at.articleId) as nr, at.tagId, t.title FROM article_tags AS at, tags AS t WHERE at.tagId=t.tagId GROUP BY at.tagId ORDER BY nr DESC';
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
	}
	
	public function tag( $tagName ){
		$model	= new Model_Tag( $this->env );
		$tag	= $model->getByIndex( 'title', $tagName );
		$list	= array();
		$relatedTags	= array();
		if( $tag ){
			$model		= new Model_ArticleTag( $this->env );
			$relations	= $model->getAllByIndex( 'tagId', $tag->tagId );
			$model		= new Model_Article( $this->env );
			foreach( $relations as $relation ){
				$article	= $model->get( $relation->articleId );
				$list[$article->articleId]	= $article;
			}
			krsort( $list );
			
			$model			= new Model_Tag( $this->env );
			$relatedTags	= $model->getRelatedTags( $tag->tagId );
		}
		$this->setData( array(
			'articles'	=> $list,
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