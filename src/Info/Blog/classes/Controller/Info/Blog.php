<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Info_Blog extends Controller
{
	protected Model_Blog_Category $modelCategory;
	protected Model_Blog_Comment $modelComment;
	protected Model_Blog_Post $modelPost;
	protected Model_User $modelUser;
	protected MessengerResource $messenger;

	public static function getUriPart( string $label, string $delimiter = "_" ): string
	{
		$label	= str_replace( ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'], ['ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss'], $label );
		$label	= preg_replace( "/[^a-z0-9 ]/i", "", $label );
		return  preg_replace( "/ +/", $delimiter, $label );
	}

	/**
	 *	@param		int|string		$postId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function comment( int|string $postId ): void
	{
		if( !$postId )
			$this->restart( NULL, TRUE );
		$post		= $this->checkPost( $postId );
		$request	= $this->env->getRequest();
		$language	= $this->env->getLanguage();

		if( $request->has( 'save' ) ){
			$data		= [
				'postId'	=> $post->postId,
				'language'	=> $language->getLanguage(),
				'title'		=> $request->get( 'title' ),
				'username'	=> $request->get( 'username' ),
				'email'		=> $request->get( 'email' ),
				'content'	=> $request->get( 'content' ),
				'createdAt'	=> time(),
			];
			$commentId	= $this->modelComment->add( $data );
			$words		= (object )$this->getWords( 'msg' );
			$this->messenger->noteSuccess( $words->successSaved );
			$this->informAboutNewComment( $commentId );
		}
		$this->restart( View_Info_Blog::renderPostUrlStatic( $this->env, $post ) );
	}

	public function index( $page = NULL ): void
	{
		$limit		= 5;
		$offset		= (int) $page * $limit;
		$orders		= ['createdAt' => 'DESC'];
		$conditions	= ['status' => '> 0'];
		$limits		= [$limit, $offset];
		$posts		= $this->modelPost->getAll( $conditions, $orders, $limits );
		foreach( $posts as $post ){
			$post->author	= $this->modelUser->get( $post->authorId );
		}
		$words		= $this->getWords( 'comment' );
//print_m( $words );die;

		$this->addData( 'posts', $posts );
		$this->addData( 'page', $page );
	}

	/**
	 *	@param		int|string|NULL		$postId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function post( int|string|NULL $postId = NULL ): void
	{
		if( !$postId )
			$this->restart( NULL, TRUE );
		$postId			= (int) preg_replace( '/^([0-9]+)-?.*$/', '\\1', $postId );
		$post			= $this->checkPost( $postId );
		$this->modelPost->edit( $postId, [															//  save in post
			'nrViews'	=> $post->nrViews + 1,														//  ... increased views
			'viewedAt'	=> time(),																	//  ... last view timestamp
		] );
		$post->author	= $this->modelUser->get( $post->authorId );									//  extend post by author
		$post->comments	= $this->modelComment->getAllByIndices( [									//  collect post comments
			'postId'	=> $post->postId,															//  ... related to this post
			'status'	=> '>= 0'																	//  ... and visible
		] );
		$this->addData( 'post', $post );															//  assign post data to template

		$indices	= ['status' => 1];
		$orders		= ['createdAt' => 'DESC'];
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

	protected function __onInit(): void
	{
		$this->modelCategory	= new Model_Blog_Category( $this->env );
		$this->modelComment		= new Model_Blog_Comment( $this->env );
		$this->modelPost		= new Model_Blog_Post( $this->env );
		$this->modelUser		= new Model_User( $this->env );
		$this->messenger		= $this->env->getMessenger();

		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.info_blog.', TRUE );
		if( $this->moduleConfig->get( 'mail' ) ){
			if( !$this->env->getModules()->has( 'Resource_Mail' ) ){
				$words		= (object )$this->getWords( 'msg' );
				$this->messenger->noteFailure( $words->failureMailModuleMissing );
			}
		}
		$this->addData( 'moduleConfig', $this->moduleConfig );
	}

	/**
	 *	@param		int|string		$postId
	 *	@param		bool			$strict
	 *	@return		object
	 *	@throws	\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkPost( int|string $postId, bool $strict = FALSE ): object
	{
		$post	= $this->modelPost->get( (int) $postId );
		if( !$post ){
			if( $strict )
				throw new OutOfRangeException( 'Invalid post ID' );
			$words		= (object )$this->getWords( 'msg' );
			$this->messenger->noteError( $words->errorInvalidPostId );
			$this->restart( NULL, TRUE );
		}
		return $post;
	}

	/**
	 *	@param		string $commentId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function informAboutNewComment( string $commentId ): void
	{
		if( !$this->moduleConfig->get( 'mail' ) )												//  do not send mails to participants
			return;
		$request	= $this->env->getRequest();
		$language	= $this->env->getLanguage();
		/** @var Logic_Mail $logic */
		$logic		= Logic_Mail::getInstance( $this->env );										//  get mailer logic
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
		$indices	= ['postId' => $post->postId, 'status' => '>= 0'];								//  get all visible post comments
		foreach( $this->modelComment->getAllByIndices( $indices ) as $item ){						//  find former comment authors
			if( empty( $item->email ) )																//  comment without email address
				continue;																			//  cannot inform
			if( $item->email == $request->get( 'email' ) )										//  comment by current comment author
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
			$receiver	= (object) ['username' => $item->username, 'email' => $item->email];		//  receiver is former comment author
			$logic->handleMail( $mail, $receiver, $language->getLanguage() );						//  enqueue mail
		}
	}
}
