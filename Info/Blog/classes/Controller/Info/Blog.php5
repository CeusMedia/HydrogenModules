<?php
class Controller_Info_Blog extends CMF_Hydrogen_Controller{

	protected $modelPost;
	protected $modelUser;
	protected $messenger;

	protected function __onInit(){
		$this->modelCategory	= new Model_Blog_Category( $this->env );
		$this->modelComment		= new Model_Blog_Comment( $this->env );
		$this->modelPost		= new Model_Blog_Post( $this->env );
		$this->modelUser		= new Model_User( $this->env );
		$this->messenger		= $this->env->getMessenger();

		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.info_blog.', TRUE );
		if( $this->moduleConfig->get( 'mail' ) )
			if( !$this->env->getModules()->has( 'Resource_Mail' ) )
				$this->messenger->noteFailure( 'Module Info:Blog has mails enabled, but module Resource:Mail is missing.' );
		$this->addData( 'moduleConfig', $this->moduleConfig );
	}

	protected function checkPost( $postId ){
		$post	= $this->modelPost->get( (int) $postId );
		if( !$post ){
			$this->messenger->noteError( 'Invalid post ID.' );
			$this->restart( NULL, TRUE );
		}
		return $post;
	}

	public function comment( $postId ){
		if( !$postId )
			$this->restart( NULL, TRUE );
		$post		= $this->checkPost( $postId );
		$request	= $this->env->getRequest();
		$language	= $this->env->getLanguage();

		if( $request->has( 'save' ) ){
			$data		= array(
				'postId'	=> $post->postId,
				'language'	=> $language->getLanguage(),
				'title'		=> $request->get( 'title' ),
				'username'	=> $request->get( 'username' ),
				'email'		=> $request->get( 'email' ),
				'content'	=> $request->get( 'content' ),
				'createdAt'	=> time(),
			);
			$commentId	= $this->modelComment->add( $data );

			$data	= array(
				'comment'	=> $this->modelComment->get( $commentId ),
				'post'		=> $post,
			);
			if( $this->moduleConfig->get( 'mail' ) ){												//  send mails to participants
				$logic		= new Logic_Mail( $this->env );											//  get mailer logic

				$mail		= new Mail_Info_Blog_Comment( $this->env, $data );						//  generate mail to post author
				$postAuthor	= $this->modelUser->get( $post->authorId );								//  set post author as mail receiver
				$logic->handleMail( $mail, $postAuthor, $language->getLanguage() );					//  enqueue mail

				$addresses	= array();
				$indices	= array( 'postId' => $post->postId, 'status' => '>=0' );				//  get all visible post comments
				foreach( $this->modelComment->getAllByIndices( $indices ) as $item ){				//  find former comment authors
					if( empty( $item->email ) )														//  comment without email address
						continue;																	//  cannot inform
					if( $item->email == $request->get( 'email' ) )									//  comment by current comment author
						continue;																	//  no need to inform
					if( $item->authorId == $post->authorId )										//  comment by original author
						continue;																	//  already has been informed
					if( $item->authorId ){															//  comment by authenticated user
						$commentAuthor	= $this->modelUser->get( $item->authorId );					//  get comment user
						if( $commentAuthor->status < 0 )											//  user is not active (anymore)
							continue;																//  skip
						$item->username	= $commentAuthor->username;									//  not receiver username for mailer
						$item->email	= $commentAuthor->email;									//  not receiver email address for mailer
					}
					if( in_array( $item->email, $addresses ) )										//  many comments by one author
						continue;																	//  just send one mail
					$addresses[]	= $item->email;													//  note used email address
					$data['myComment']	= $item;													//  decorate mail data by own former comment
					$mail		= new Mail_Info_Blog_FollowUp( $this->env, $data );					//  generate mail
					$receiver	= array( 'username' => $item->username, 'email' => $item->email );	//  receiver is former comment author
					$logic->handleMail( $mail, $receiver, $language->getLanguage() );				//  enqueue mail
				}
			}
			$this->messenger->noteSuccess( 'Your comment has been added.' );
		}
		$this->restart( 'post/'.$post->postId, TRUE );
	}

	public function index( $page = NULL ){
		$limit		= 5;
		$offset		= (int) $page * $limit;
		$orders		= array( 'createdAt' => 'DESC' );
		$conditions	= array( 'status' => '>0' );
		$limits		= array( $limit, $offset );
		$posts		= $this->modelPost->getAll( $conditions, $orders, $limits );
		foreach( $posts as $post ){
			$post->author	= $this->modelUser->get( $post->authorId );
		}
		$this->addData( 'posts', $posts );
		$this->addData( 'page', $page );
	}

	public function post( $postId = NULL ){
		if( !$postId )
			$this->restart( NULL, TRUE );
		$post			= $this->checkPost( $postId );
		$post->author	= $this->modelUser->get( $post->authorId );									//  extend post by author
		$post->comments	= $this->modelComment->getAllByIndices( array(								//  collect post comments
			'postId'	=> $post->postId,															//  ... related to this post
			'status'	=> '>=0'																	//  ... and visible
		) );
		$this->addData( 'post', $post );															//  assign post data to template

		$indices	= array( 'status' => 1 );
		$orders		= array( 'createdAt' => 'DESC' );
		$posts		= $this->modelPost->getAllByIndices( $indices, $orders );
		$lastPost	= NULL;
		$nextPost	= NULL;
		foreach( $posts as $nr => $item ){
			if( $item->postId == $post->postId ){
				if( isset( $posts[$nr + 1] ) )
					$nextPost	= $posts[$nr + 1];
				break;
			}
			$lastPost	= $item;
		}
		$this->addData( 'prevPost', $lastPost );
		$this->addData( 'nextPost', $nextPost );
	}
}
