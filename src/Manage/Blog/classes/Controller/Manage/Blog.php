<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\PartitionSession;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Blog extends Controller
{
	protected MessengerResource $messenger;
	protected Model_Blog_Category $modelCategory;
	protected Model_Blog_Comment $modelComment;
	protected Model_Blog_Post $modelPost;
	protected Model_User $modelUser;
	protected Dictionary $moduleConfig;
	protected HttpRequest $request;
	protected PartitionSession $session;

	/**
	 *	@param		string		$label
	 *	@param		string		$delimiter
	 *	@return		string
	 */
	public static function getUriPart( string $label, string $delimiter = '_' ): string
	{
		$label	= str_replace( ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'], ['ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss'], $label );
		$label	= preg_replace( "/[^a-z0-9 ]/i", "", $label );
		return preg_replace( "/ +/", $delimiter, $label );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		/** @var Logic_Authentication $logicAuth */
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$language		= $this->env->getLanguage();
		if( $this->request->has( 'save' ) ){
			if( !trim( $this->request->get( 'title' ) ) )
				$this->messenger->noteError( 'Title is missing.' );
			else if( !trim( strip_tags( $this->request->get( 'content' ) ) ) )
				$this->messenger->noteError( 'Post content is missing.' );
			else if( !trim( strip_tags( $this->request->get( 'abstract' ) ) ) )
				$this->messenger->noteError( 'Post abstract is missing.' );
			else{
				$data		= [
					'authorId'		=> $this->request->get( 'authorId' ),
					'categoryId'	=> (int) $this->request->get( 'categoryId' ),
					'status'		=> $this->request->get( 'status' ),
					'language'		=> $this->request->get( 'language' ),
					'title'			=> $this->request->get( 'title' ),
					'content'		=> $this->request->get( 'content' ),
					'abstract'		=> $this->request->get( 'abstract' ),
					'createdAt'		=> time(),
				];
				$postId	= $this->modelPost->add( $data, FALSE );
				$this->messenger->noteSuccess( 'Der neue Eintrag wurde gespeichert.' );
				$this->messenger->noteNotice( 'Bitte überarbeite jetzt deinen Eintrag!<br/>Wenn du fertig bist, kannst du den Eintrag mit dem Status "öffentlich" sichtbar machen.' );
				$this->restart( './manage/blog/edit/'.$postId );
			}

		}
		$data	= [];
		foreach( $this->modelPost->getColumns() as $column ){
			$data[$column]	= $this->request->get( $column );
		}
		if( empty( $data['authorId'] ) )
			$data['authorId']	= $logicAuth->getCurrentUserId();
		if( empty( $data['language'] ) )
			$data['language']	= $language->getLanguage();
		$data['status']		= 0;

		$categories		= $this->modelCategory->getAllByIndices( ['status' => '>= 0'] );		//
		/** @var array<Entity_User> $users */
		$users			= $this->modelUser->getAll( ['status' => '> 0'] );
		$this->addData( 'post', (object) $data );
		$this->addData( 'users', $users );
		$this->addData( 'categories', $categories );
	}

	/**
	 *	@param		int|string		$postId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addComment( int|string $postId ): void
	{
		if( !$postId )
			$this->restart( NULL, TRUE );
		$post		= $this->checkPost( $postId );
		/** @var Logic_Authentication $logicAuth */
		$logicAuth	= Logic_Authentication::getInstance( $this->env );
		$user		= $logicAuth->getCurrentUser();
//		print_m( $user );die;
		$language	= $this->env->getLanguage();

		if( $this->request->has( 'save' ) ){
			$data		= [
				'postId'	=> $post->postId,
				'language'	=> $language->getLanguage(),
				'title'		=> $this->request->get( 'title' ),
				'username'	=> $this->request->get( 'username' ),
				'email'		=> $this->request->get( 'email' ),
				'content'	=> $this->request->get( 'content' ),
				'createdAt'	=> time(),
			];
			$commentId	= $this->modelComment->add( $data );
			$this->messenger->noteSuccess( 'Your comment has been added.' );
//			$this->informAboutNewComment( $commentId );
		}
		$this->restart( 'post/'.$post->postId, TRUE );
	}

	/**
	 *	@param		int|string|NULL		$postId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string|NULL $postId = NULL ): void
	{
		if( !$postId )
			$this->restart( NULL, TRUE );
		$post			= $this->checkPost( $postId );

		if( $this->request->has( 'save' ) ){
			$data	= [
				'authorId'		=> $this->request->get( 'authorId' ),
				'categoryId'	=> $this->request->get( 'categoryId' ),
				'status'		=> $this->request->get( 'status' ),
				'language'		=> $this->request->get( 'language' ),
				'title'			=> $this->request->get( 'title' ),
				'content'		=> $this->request->get( 'content' ),
				'abstract'		=> $this->request->get( 'abstract' ),
				'modifiedAt'	=> time(),
			];
			$this->modelPost->edit( $post->postId, $data, FALSE );
			$this->messenger->noteSuccess( 'Der Eintrag wurde gespeichert.' );
			$this->restart( 'edit/'.$post->postId, TRUE );
		}

		$post->author	= $this->modelUser->get( $post->authorId );									//  extend post by author
		$post->comments	= $this->modelComment->getAllByIndices( [									//  collect post comments
			'postId'	=> $post->postId,															//  ... related to this post
			'status'	=> '>= 0'																	//  ... and visible
		] );
		$categories		= $this->modelCategory->getAllByIndices( ['status' => '>= 0'] );		//
		/** @var array<Entity_User> $users */
		$users			= $this->modelUser->getAll( ['status' => '> 0'] );

		$this->addData( 'post', $post );															//  assign post data to template
		$this->addData( 'categories', $categories );
		$this->addData( 'users', $users );
	}

	public function filter( $reset = NULL ): void
	{
		if( $reset ){
			$this->session->remove( 'filter_manage_blog_status' );
			$this->session->remove( 'filter_manage_blog_categoryId' );
		}
		$this->session->set( 'filter_manage_blog_status', $this->request->get( 'status' ) );
		$this->session->set( 'filter_manage_blog_categoryId', $this->request->get( 'categoryId' ) );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		$page
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( $page = NULL ): void
	{
		$filterStatus		= $this->session->get( 'filter_manage_blog_status' );
		$filterCategoryId	= $this->session->get( 'filter_manage_blog_categoryId' );

		$limit		= 15;
		$offset		= (int) $page * $limit;
		$orders		= ['createdAt' => 'DESC'];
		$conditions	= [];
		if( strlen( $filterStatus ) )
			$conditions['status']	= $filterStatus;
		if( strlen( $filterCategoryId ) )
			$conditions['categoryId']	= $filterCategoryId;
		$limits		= [$offset, $limit];
		$total		= $this->modelPost->count( $conditions );
		$posts		= $this->modelPost->getAll( $conditions, $orders, $limits );
		foreach( $posts as $post ){
			$post->author	= $this->modelUser->get( $post->authorId );
			$post->category	= $this->modelCategory->get( $post->categoryId );
		}

		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterCategoryId', $filterCategoryId );

		$this->addData( 'posts', $posts );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $total / $limit ) );
		$this->addData( 'categories', $this->modelCategory->getAll() );
	}

	protected function __onInit(): void
	{
		$this->modelCategory	= new Model_Blog_Category( $this->env );
		$this->modelComment		= new Model_Blog_Comment( $this->env );
		$this->modelPost		= new Model_Blog_Post( $this->env );
		$this->modelUser		= new Model_User( $this->env );
		$this->messenger		= $this->env->getMessenger();
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();

		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.manage_blog.', TRUE );
		if( $this->moduleConfig->get( 'mail' ) )
			if( !$this->env->getModules()->has( 'Resource_Mail' ) )
				$this->messenger->noteFailure( 'Module Info:Blog has mails enabled, but module Resource:Mail is missing.' );
		$this->addData( 'moduleConfig', $this->moduleConfig );
	}

	/**
	 *	@param		int|string		$postId
	 *	@param		bool			$strict
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkPost( int|string $postId, bool $strict = FALSE ): ?object
	{
		$post	= $this->modelPost->get( (int) $postId );
		if( !$post ){
			if( $strict )
				throw new OutOfRangeException( 'Invalid post ID' );
			$this->messenger->noteError( 'Invalid post ID.' );
			$this->restart( NULL, TRUE );
		}
		return $post;
	}

/*	protected function informAboutNewComment( $commentId ){
		if( !$this->moduleConfig->get( 'mail' ) )													//  do not send mails to participants
			return;
		$logic		= Logic_Mail::getInstance( $this->env );										//  get mailer logic
		$language	= $this->env->getLanguage();
		$comment	= $this->modelComment->get( $commentId );
		$post		= $this->checkPost( $comment->postId );
		$data	= [
			'comment'	=> $comment,
			'post'		=> $post,
		];

		$mail		= new Mail_Info_Blog_Comment( $this->env, $data );								//  generate mail to post author
		$postAuthor	= $this->modelUser->get( $post->authorId );										//  set post author as mail receiver
		$logic->handleMail( $mail, $postAuthor, $language->getLanguage() );							//  enqueue mail

		$addresses	= [];
		$indices	= ['postId' => $post->postId, 'status' => '>= 0'];						//  get all visible post comments
		foreach( $this->modelComment->getAllByIndices( $indices ) as $item ){						//  find former comment authors
			if( empty( $item->email ) )																//  comment without email address
				continue;																			//  cannot inform
			if( $item->email == $this->request->get( 'email' ) )											//  comment by current comment author
				continue;																			//  no need to inform
			if( $item->authorId == $post->authorId )												//  comment by original author
				continue;																			//  already has been informed
			if( $item->authorId ){																	//  comment by authenticated user
				$commentAuthor	= $this->modelUser->get( $item->authorId );							//  get comment user
				if( $commentAuthor->status < 0 )													//  user is not active (anymore)
					continue;																		//  skip
				$item->username	= $commentAuthor->username;											//  not receiver username for mailer
				$item->email	= $commentAuthor->email;											//  not receiver email address for mailer
			}
			if( in_array( $item->email, $addresses ) )												//  many comments by one author
				continue;																			//  just send one mail
			$addresses[]	= $item->email;															//  note used email address
			$data['myComment']	= $item;															//  decorate mail data by own former comment
			$mail		= new Mail_Info_Blog_FollowUp( $this->env, $data );							//  generate mail
			$receiver	= ['username' => $item->username, 'email' => $item->email];			//  receiver is former comment author
			$logic->handleMail( $mail, $receiver, $language->getLanguage() );						//  enqueue mail
		}
	}*/
}
