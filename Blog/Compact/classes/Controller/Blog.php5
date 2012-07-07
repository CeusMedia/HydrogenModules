<?php
class Controller_Blog extends CMF_Hydrogen_Controller{

	/**	@var	Model_Article		$model		Article model instance */
	protected $model;

	protected $path;
	
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Environment object
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		parent::__construct( $env );
		$config			= $env->getConfig();
		$this->model	= new Model_Article( $env );
	}

	/**
	 *	Adds an article, therefore receives title and content.
	 *	@access		public
	 *	@return		void
	 *	@throws		InvalidArgumentException	if title or content is missing
	 */
	public function add(){
		$request	= $this->env->getRequest();
		if( $request->get( 'do' ) == 'save' ){
			if( !strlen( trim( $request->get( 'title' ) ) ) )
				throw new InvalidArgumentException( 'Article title is missing' ) ;
			if( !strlen( trim( $request->get( 'content' ) ) ) )
				throw new InvalidArgumentException( 'Article content is missing' ) ;
			
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
			$this->env->getMessenger()->noteSuccess( 'Der Artikel wurde gespeichert.' );
			$this->restart( './blog/edit/'.$articleId );
		}
	}

	public function addTag( $articleId ){
		$request	= $this->env->getRequest();
		if( (int) $articleId < 1 )
			$this->restart( './blog/' );

		$tagName	= $request->get( 'tag' );
		if( !strlen( $tagName ) )
			$this->restart( './blog/edit/'.$articleId );
		$modelTag	= new Model_Tag( $this->env );
		$tag		= $modelTag->getByIndex( 'title', $tagName );
		if( $tag ){
			$tagId	= $tag->tagId;
			$number	= $tag->number;
			$indices	= array(
				'articleId'	=> $articleId,
				'tagId'		=> $tagId,
			);
			if( $model->getByIndices( $indices ) )
				$this->restart( './blog/edit/'.$articleId );
		}
		else{
			$tagId	= $modelTag->add( array( 'title' => $tagName ) );
			$number	= 0;
		}
		$model	= new Model_ArticleTag( $this->env );
		$data	= array(
			'articleId'	=> $articleId,
			'tagId'		=> $tagId,
		);
		$model->add( $data );
		$modelTag->edit( $tagId, array( 'number' => ++$number ) );
		$this->restart( './blog/edit/'.$articleId );
	}

	public function article( $articleId, $title )
	{
		if( (int) $articleId < 1 )
			$this->restart( './blog/' );
		$data	= array(
			'articles'	=> $this->model->getAll(),
			'article'	=> $this->model->get( $articleId ),
			'tags'		=> $this->model->getArticleTags( $articleId ),
			'authors'	=> $this->model->getArticleAuthors( $articleId ),
			'articleId'	=> rawurldecode( $articleId ),
		);
		$this->setData( $data );
	}

	public function edit( $articleId ){
		if( (int) $articleId < 1 )
			$this->restart( './blog/' );
		$request	= $this->env->getRequest();
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

	public function index( $page = 0, $limit = NULL )
	{
		$perPage	= abs( (int) $this->env->getConfig()->get( 'module.blog_compact.perPage' ) );
		$limit		= !is_null( $limit ) ? $limit : ( $perPage ? $perPage : 10 );
		$offset		= $page * $limit;
		$limits		= array( $offset, $limit );
		$conditions	= array( 'status' => 1 );
		$orders		= array( 'articleId' => 'DESC' );
		$articles	= $this->model->getAll( $conditions, $orders, $limits );
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
			'number'	=> $this->model->count( $conditions ),
			'topTags'	=> $topTags,
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