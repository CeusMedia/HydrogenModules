<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Info_Forum extends AjaxController
{
	protected Model_Forum_Post $modelPost;
	protected Model_Forum_Thread $modelThread;
	protected Model_Forum_Topic $modelTopic;

	/**
	 *	@param		int|string		$threadId
	 *	@param		int|string		$lastPostId
	 *	@return		int
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function countUpdates( int|string $threadId, int|string $lastPostId ): int
	{
		$thread		= $this->modelThread->get( $threadId );
		if( !$thread )
			return $this->respondError( 404, 'Invalid thread ID', 404 );

		$conditions		= ['threadId' => $threadId, 'postId' => '> '.$lastPostId];
		$orders			= ['postId' => 'ASC'];
		$posts			= $this->modelPost->getAll( $conditions, $orders );
		$post			= array_pop( $posts );

		return $this->respondData( [
			'status'	=> 'data',
			'data'		=> [
				'count'		=> count( $posts ),
				'postId'	=> $post->postId
			]
		] );
	}

	/**
	 *	@return		int
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function editPost(): int
	{
		$postId		= $this->request->get( 'postId' );
		$content	= $this->request->get( 'content' );
		if( $postId && $content ){
			$post	= $this->modelPost->get( (int) $postId );
			if( $post->content !== $content ){
				$this->modelPost->edit( (int) $postId, ['content' => $content, 'modifiedAt' => time()] );
				return $this->respondData( TRUE );
			}
		}
		return $this->respondData( FALSE );
	}

	/**
	 *	@param		int|string		$postId
	 *	@return		int
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getPost( int|string $postId ): int
	{
		$post		= $this->modelPost->get( $postId );
		if( !$post )
			$post	= ['content', 'Error: Invalid post ID: '.$postId];
		return $this->respondData( $post );
	}

	/**
	 *	@return		int
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function renameThread(): int
	{
		$threadId	= $this->request->get( 'threadId' );
		$name		= $this->request->get( 'name' );
		if( $threadId && $name ){
			$this->modelThread->edit( (int) $threadId, ['title' => $name] );
			return $this->respondData( TRUE );
		}
		return $this->respondData( FALSE );
	}

	/**
	 *	@return		int
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function renameTopic(): int
		{
		$topicId	= $this->request->get( 'topicId' );
		$name		= $this->request->get( 'name' );
		if( $topicId && $name ){
			$this->modelTopic->edit( (int) $topicId, ['title' => $name] );
			return $this->respondData( TRUE );
		}
		return $this->respondData( FALSE );
	}

	/**
	 *	@param		int|string		$threadId
	 *	@return		int
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function starThread( int|string $threadId ): int
	{
		$thread		= $this->modelThread->get( (int) $threadId );
		if( $thread ){
			$this->modelThread->edit( (int) $threadId, ['type' => $thread->type ? 0 : 1] );
			return $this->respondData( TRUE );
		}
		return $this->respondData( FALSE );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->modelPost	= new Model_Forum_Post( $this->env );
		$this->modelThread	= new Model_Forum_Thread( $this->env );
		$this->modelTopic	= new Model_Forum_Topic( $this->env );
	}
}
