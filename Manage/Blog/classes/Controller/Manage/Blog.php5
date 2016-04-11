<?php
class Controller_Manage_Blog extends CMF_Hydrogen_Controller{

	protected $messenger;
	protected $modelCategory;
	protected $modelComment;
	protected $modelPost;
	protected $modelUser;
	protected $moduleConfig;
	protected $request;
	protected $session;

	protected function __onInit(){
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

	static public function getUriPart( $label, $delimiter = "_" ){
		$label	= str_replace( array( 'ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß' ), array( 'ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss' ), $label );
		$label	= preg_replace( "/[^a-z0-9 ]/i", "", $label );
		$label	= preg_replace( "/ +/", $delimiter, $label );
		return $label;
	}

	static public function ___onTinyMCE_getLinkList( $env, $context, $module, $arguments = array() ){
		$frontend		= Logic_Frontend::getInstance( $env );
		if( !$frontend->hasModule( 'Info_Blog' ) )
			return;

		$words		= $env->getLanguage()->getWords( 'manage/blog' );
		$model		= new Model_Blog_Post( $env );
		$list		= array();
		foreach( $model->getAllByIndex( 'status', 1 ) as $nr => $post ){
			$list[$post->title.$nr]	= (object) array(
				'title'	=> $post->title,
				'value'	=> './info/blog/post/'.$post->postId.'-'.self::getUriPart( $post->title )
			);
		}
		if( $list ){
			ksort( $list );
			$list	= array( (object) array(
				'title'	=> $words['tinyMCE']['prefix'],
				'menu'	=> array_values( $list ),
			) );
	//		$context->list	= array_merge( $context->list, array_values( $list ) );
			$context->list	= array_merge( $context->list, $list );
		}
	}

	public function add(){
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
				$data		= array(
					'authorId'		=> $logicAuth->getCurrentUserId(),
					'categoryId'	=> (int) $this->request->get( 'categoryId' ),
					'status'		=> $this->request->get( 'status' ),
					'language'		=> $this->request->get( 'language' ),
					'title'			=> $this->request->get( 'title' ),
					'content'		=> $this->request->get( 'content' ),
					'abstract'		=> $this->request->get( 'abstract' ),
					'createdAt'		=> time(),
				);
				$postId	= $this->modelPost->add( $data );
				$this->messenger->noteSuccess( 'Der neue Eintrag wurde gespeichert.' );
				$this->messenger->noteNotice( 'Bitte überarbeite jetzt deinen Eintrag!<br/>Wenn du fertig bist, kannst du den Eintrag mit dem Status "öffentlich" sichtbar machen.' );
				$this->restart( './manage/blog/edit/'.$postId );
			}

		}
		$data	= array();
		foreach( $this->modelPost->getColumns() as $column ){
			$data[$column]	= $this->request->get( $column );
		}
		if( empty( $data['authorId'] ) )
			$data['authorId']	= $logicAuth->getCurrentUserId();
		if( empty( $data['language'] ) )
			$data['language']	= $language->getLanguage();
		$data['status']		= 0;

		$categories			= $this->modelCategory->getAllByIndices( array( 'status' => '>=0' ) );		//
		$users				= $this->modelUser->getAll( array( 'status' => '>0' ) );
		$this->addData( 'post', (object) $data );
		$this->addData( 'users', $users );
		$this->addData( 'categories', $categories );
	}

	public function addComment( $postId ){
		if( !$postId )
			$this->restart( NULL, TRUE );
		$post		= $this->checkPost( $postId );
		$logicAuth	= Logic_Authentication::getInstance( $this->env );
		$user		= $logicAuth->getCurrentUser();
		print_m( $user );die;
		$language	= $this->env->getLanguage();

		if( $this->request->has( 'save' ) ){
			$data		= array(
				'postId'	=> $post->postId,
				'language'	=> $language->getLanguage(),
				'title'		=> $this->request->get( 'title' ),
				'username'	=> $this->request->get( 'username' ),
				'email'		=> $this->request->get( 'email' ),
				'content'	=> $this->request->get( 'content' ),
				'createdAt'	=> time(),
			);
			$commentId	= $this->modelComment->add( $data );
			$this->messenger->noteSuccess( 'Your comment has been added.' );
//			$this->informAboutNewComment( $commentId );
		}
		$this->restart( 'post/'.$post->postId, TRUE );
	}

	protected function checkPost( $postId, $strict = FALSE ){
		$post	= $this->modelPost->get( (int) $postId );
		if( !$post ){
			if( $strict )
				throw new OutOfRangeException( 'Invalid post ID' );
			$this->messenger->noteError( 'Invalid post ID.' );
			$this->restart( NULL, TRUE );
		}
		return $post;
	}

	public function edit( $postId = NULL ){
		if( !$postId )
			$this->restart( NULL, TRUE );
		$post			= $this->checkPost( $postId );

		if( $this->request->has( 'save' ) ){
			$data	= array(
				'authorId'		=> $this->request->get( 'authorId' ),
				'categoryId'	=> $this->request->get( 'categoryId' ),
				'status'		=> $this->request->get( 'status' ),
				'language'		=> $this->request->get( 'language' ),
				'title'			=> $this->request->get( 'title' ),
				'content'		=> $this->request->get( 'content' ),
				'abstract'		=> $this->request->get( 'abstract' ),
				'modifiedAt'	=> time(),
			);
			$this->modelPost->edit( $post->postId, $data, FALSE );
			$this->messenger->noteSuccess( 'Der Eintrag wurde gespeichert.' );
			$this->restart( 'edit/'.$post->postId, TRUE );
		}

		$post->author	= $this->modelUser->get( $post->authorId );									//  extend post by author
		$post->comments	= $this->modelComment->getAllByIndices( array(								//  collect post comments
			'postId'	=> $post->postId,															//  ... related to this post
			'status'	=> '>=0'																	//  ... and visible
		) );
		$categories		= $this->modelCategory->getAllByIndices( array( 'status' => '>=0' ) );		//
		$users			= $this->modelUser->getAll( array( 'status' => '>0' ) );

		$this->addData( 'post', $post );															//  assign post data to template
		$this->addData( 'categories', $categories );
		$this->addData( 'users', $users );
	}

	public function filter( $reset = NULL ){
		if( $reset ){
			$this->session->remove( 'filter_manage_blog_status' );
			$this->session->remove( 'filter_manage_blog_categoryId' );
		}
		$this->session->set( 'filter_manage_blog_status', $this->request->get( 'status' ) );
		$this->session->set( 'filter_manage_blog_categoryId', $this->request->get( 'categoryId' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $page = NULL ){

		$filterStatus		= $this->session->get( 'filter_manage_blog_status' );
		$filterCategoryId	= $this->session->get( 'filter_manage_blog_categoryId' );

		$limit		= 15;
		$offset		= (int) $page * $limit;
		$orders		= array( 'createdAt' => 'DESC' );
		$conditions	= array();
		if( strlen( $filterStatus ) )
			$conditions['status']	= $filterStatus;
		if( strlen( $filterCategoryId ) )
			$conditions['categoryId']	= $filterCategoryId;
		$limits		= array( $offset, $limit );
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

/*	protected function informAboutNewComment( $commentId ){
		if( !$this->moduleConfig->get( 'mail' ) )													//  do not send mails to participants
			return;
		$logic		= new Logic_Mail( $this->env );													//  get mailer logic
		$language	= $this->env->getLanguage();
		$comment	= $this->modelComment->get( $commentId );
		$post		= $this->checkPost( $comment->postId );
		$data	= array(
			'comment'	=> $comment,
			'post'		=> $post,
		);

		$mail		= new Mail_Info_Blog_Comment( $this->env, $data );								//  generate mail to post author
		$postAuthor	= $this->modelUser->get( $post->authorId );										//  set post author as mail receiver
		$logic->handleMail( $mail, $postAuthor, $language->getLanguage() );							//  enqueue mail

		$addresses	= array();
		$indices	= array( 'postId' => $post->postId, 'status' => '>=0' );						//  get all visible post comments
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
			$receiver	= array( 'username' => $item->username, 'email' => $item->email );			//  receiver is former comment author
			$logic->handleMail( $mail, $receiver, $language->getLanguage() );						//  enqueue mail
		}
	}*/
}
