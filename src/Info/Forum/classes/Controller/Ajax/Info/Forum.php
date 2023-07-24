<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Info_Forum extends AjaxController
{
	protected Model_Forum_Post $modelPost;
	protected Model_Forum_Thread $modelThread;
	protected Model_Forum_Topic $modelTopic;

	public function countUpdates( $threadId, $lastPostId ): int
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
	 * @return void
	 */
	public function editPost(): void
	{
		$postId		= $this->request->get( 'postId' );
		$content	= $this->request->get( 'content' );
		if( $postId && $content ){
			$post	= $this->modelPost->get( (int) $postId );
			if( $post->content !== $content )
				$this->modelPost->edit( (int) $postId, array( 'content' => $content, 'modifiedAt' => time() ) );
		}
		exit;
	}

	public function getPost( string $postId ): void
	{
		$post		= $this->modelPost->get( $postId );
		if( !$post )
			$post	= ['content', 'Error: Invalid post ID: '.$postId];
		$this->respondData( $post );
	}

	public function renameThread(): void
	{
		$threadId	= $this->request->get( 'threadId' );
		$name		= $this->request->get( 'name' );
		if( $threadId && $name ){
			$this->modelThread->edit( (int) $threadId, ['title' => $name] );
		}
		exit;
	}

	public function renameTopic(): void
		{
		$topicId	= $this->request->get( 'topicId' );
		$name		= $this->request->get( 'name' );
		if( $topicId && $name ){
			$this->modelTopic->edit( (int) $topicId, ['title' => $name] );
		}
		exit;
	}

	public function starThread( $threadId ): void
	{
		$thread		= $this->modelThread->get( (int) $threadId );
		if( $thread ){
			$this->modelThread->edit( (int) $threadId, ['type' => $thread->type ? 0 : 1] );
		}
		exit;
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
