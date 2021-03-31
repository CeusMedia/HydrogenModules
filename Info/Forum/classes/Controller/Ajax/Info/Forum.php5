<?php
class Controller_Ajax_Info_Forum extends CMF_Hydrogen_Controller_Ajax
{
	public function countUpdates( $threadId, $lastPostId )
	{
		$thread		= $this->modelThread->get( $threadId );
		if( !$thread ){
			$data	= array( 'status' => 'error', 'error' => 'invalid thread id' );
		}
		else{
			$data	= array( 'status' => 'data', 'data' => array( 'count' => 0, 'postId' => NULL ) );
			$conditions		= array( 'threadId' => $threadId, 'postId' => '> '.$lastPostId );
			$orders			= array( 'postId' => 'ASC' );
			$posts			= $this->modelPost->getAll( $conditions, $orders );
			$data['data']['count']	= count( $posts );
			$post			= array_pop( $posts );
			$data['data']['postId']	= $post->postId;
		}
		$this->respondData( $data );
	}

	public function editPost()
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

	public function getPost( $postId )
	{
		$post		= $this->modelPost->get( $postId );
		if( !$post )
			$post	= array( 'content', 'Error: Invalid post ID: '.$postId );
		$this->respondData( $post );
	}

	public function renameThread()
	{
		$threadId	= $this->request->get( 'threadId' );
		$name		= $this->request->get( 'name' );
		if( $threadId && $name ){
			$this->modelThread->edit( (int) $threadId, array( 'title' => $name ) );
		}
		exit;
	}

	public function renameTopic()
		{
		$topicId	= $this->request->get( 'topicId' );
		$name		= $this->request->get( 'name' );
		if( $topicId && $name ){
			$this->modelTopic->edit( (int) $topicId, array( 'title' => $name ) );
		}
		exit;
	}

	public function starThread( $threadId )
	{
		$thread		= $this->modelThread->get( (int) $threadId );
		if( $thread ){
			$this->modelThread->edit( (int) $threadId, array( 'type' => $thread->type ? 0 : 1 ) );
		}
		exit;
	}
}
