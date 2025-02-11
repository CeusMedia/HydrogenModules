<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Info_Blog extends AjaxController
{
	protected Model_Blog_Comment $modelComment;
	protected Model_Blog_Post $modelPost;

	/**
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function comment(): void
	{
		$request	= $this->env->getRequest();
		$language	= $this->env->getLanguage();
//		$this->checkAjaxRequest();

		try{
			$postId	= trim( $request->get( 'postId', '' ) );
			if( '' !== $postId )
				throw new InvalidArgumentException( 'Missing post ID' );
			if( !strlen( trim( $request->get( 'username' ) ) ) )
				throw new InvalidArgumentException( 'Missing username' );
			if( !strlen( trim( $request->get( 'content' ) ) ) )
				throw new InvalidArgumentException( 'Missing content' );
			$post		= $this->checkPost( $postId );
			$data		= [
				'postId'	=> $post->postId,
				'language'	=> $language->getLanguage(),
				'username'	=> $request->get( 'username' ),
				'email'		=> $request->get( 'email' ),
				'content'	=> $request->get( 'content' ),
				'createdAt'	=> time(),
			];
			$commentId	= $this->modelComment->add( $data );
//			$this->informAboutNewComment( $commentId );
			$comment	= $this->modelComment->get( $commentId );
			$data		= [
				'comment'	=> $comment,
				'html'		=> View_Info_Blog::renderCommentStatic( $this->env, $comment ),
			];
			$this->respondData( $data );
		}
		catch( Exception $e ){
			$this->respondError( 0, $e->getMessage(), 406 );
		}
	}

	protected function __onInit(): void
	{
		$this->modelComment		= new Model_Blog_Comment( $this->env );
		$this->modelPost		= new Model_Blog_Post( $this->env );
	}

	/**
	 *	@param		int|string		$postId
	 *	@return		object
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkPost( int|string $postId ): object
	{
		$post	= $this->modelPost->get( (int) $postId );
		if( $post )
			return $post;
		$words = (object) $this->env->getLanguage()->getWords( 'msg' );
		$this->respondError( 0, $words->errorInvalidPostId );
		exit;
	}
}