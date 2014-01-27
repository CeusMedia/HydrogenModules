<?php
class Controller_Info_Forum extends CMF_Hydrogen_Controller{

	/**	@var	Model_Forum_Post	$modelPost */
	protected $modelPost;
	/**	@var	Model_Forum_Thread	$modelThread */
	protected $modelThread;
	/**	@var	Model_Forum_Topic	$modelTopic */
	protected $modelTopic;
	/**	@var	array				$rights */
	protected $rights;

	public function __onInit(){
		$this->modelPost	= new Model_Forum_Post( $this->env );
		$this->modelThread	= new Model_Forum_Thread( $this->env );
		$this->modelTopic	= new Model_Forum_Topic( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->options		= $this->env->getConfig()->getAll( 'module.info_forum.' );
		$this->rights		= $this->env->getAcl()->index( 'info/forum' );
	}

	public function addPost( $threadId ){
		$request	= $this->env->getRequest();

		$thread		= $this->modelThread->get( $threadId );
		if( !$thread ){
			$this->env->getMessenger()->noteError( 'Invalid thread ID: '.$threadId );
			$this->restart( NULL, TRUE );
		}
		if( $thread->status < 0 ){
			$this->env->getMessenger()->noteError( 'Thread '.$threadId.' is no longer available' );
			$this->restart( NULL, TRUE );
		}
		if( $thread->status == 2 ){
			$this->env->getMessenger()->noteError( 'Thread '.$threadId.' is closed.' );
			$this->restart( NULL, TRUE );
		}
		
		$data		= $request->getAll();
		if( in_array( 'approvePost', $this->rights ) )
			$data['status']	= 1;
		$data['threadId']	= $threadId;
		$data['authorId']	= $this->env->getSession()->get( 'userId' );
		$data['createdAt']	= time();
		$this->modelPost->add( $data );
		$this->modelThread->edit( $threadId, array( 'modifiedAt' => time() ) );
		$this->modelTopic->edit( $thread->topicId, array( 'modifiedAt' => time() ) );
		$this->restart( 'thread/'.$threadId, TRUE );
	}

	public function addThread(){
		$request	= $this->env->getRequest();
		$data		= $request->getAll();
		$data['authorId']	= $this->env->getSession()->get( 'userId' );
		$data['createdAt']	= time();
		$threadId	= $this->modelThread->add( $data );
		$thread		= $this->modelThread->get( $threadId );
		$request->set( 'threadId', $threadId );
		$this->redirect( 'info/forum', 'addPost', array( $threadId ) );
	}

	public function addTopic(){
		$request	= $this->env->getRequest();
		$data		= $request->getAll();
		$data['authorId']	= $this->env->getSession()->get( 'userId' );
		$data['rank']		= $this->modelTopic->count();
		$data['createdAt']	= time();
		$postId		= $this->modelTopic->add( $data );
		$this->restart( NULL, TRUE );
	}

	public function ajaxRenameTopic(){
		$topicId	= $this->env->getRequest()->get( 'topicId' );
		$name		= $this->env->getRequest()->get( 'name' );
		if( $topicId && $name ){
			$this->modelTopic->edit( (int) $topicId, array( 'title' => $name ) );
		}
		exit;
	}

	public function ajaxRenameThread(){
		$threadId	= $this->env->getRequest()->get( 'threadId' );
		$name		= $this->env->getRequest()->get( 'name' );
		if( $threadId && $name ){
			$this->modelThread->edit( (int) $threadId, array( 'title' => $name ) );
		}
		exit;
	}

	public function ajaxStarThread( $threadId ){
		
		$thread		= $this->modelThread->get( (int) $threadId );
		if( $thread ){
			$this->modelThread->edit( (int) $threadId, array( 'type' => $thread->type ? 0 : 1 ) );
		}
		exit;
	}

	public function ajaxEditPost(){
		$postId		= $this->env->getRequest()->get( 'postId' );
		$content	= $this->env->getRequest()->get( 'content' );
		if( $postId && $content ){
			$post	= $this->modelPost->get( (int) $postId );
			if( $post->content !== $content )
				$this->modelPost->edit( (int) $postId, array( 'content' => $content ) );
		}
		exit;
	}

	public function ajaxGetPost( $postId ){
		$post		= $this->modelPost->get( $postId );
		if( !$post )
			$post	= array( 'content', 'Error: Invalid post ID: '.$postId );
		print( json_encode( $post ) );
		exit;
	}
			
	public function approvePost( $postId ){
		$post		= $this->modelPost->get( $postId );
		if( !$post ){
			$this->env->getMessenger()->noteError( 'Invalid post ID: '.$postId );
			$this->restart( NULL, TRUE );
		}
		$this->modelPost->edit( $postId, array( 'status' => 1 ) );
		$this->restart( './info/forum/thread/'.$post->threadId );
	}

	public function index(){
		$topics		= $this->modelTopic->getAll( array(), array( 'rank' => 'ASC' ) );
		if( count( $topics ) == 1 )
			$this->restart( './info/forum/topic/'.$topics[0]->topicId );
		foreach( $topics as $nr => $topic ){
			$threads	= $this->modelThread->getAllByIndex( 'topicId', $topic->topicId );
			$topic->threads	= count( $threads );
			$threadIds	= array();
			foreach( $threads as $thread )
				$threadIds[]	= $thread->threadId;
			$topic->posts	+= $this->modelPost->countByIndex( 'threadId', $threadIds );
		}
		$this->addData( 'rights', $this->rights );
		$this->addData( 'topics', $topics );
	}

	public function rankTopic( $topicId, $downwards = NULL ){
		$direction	= (boolean) $downwards ? +1 : -1;
		if( !( $topic = $this->modelTopic->get( (int) $topicId ) ) )
			$this->env->getMessenger()->noteError( 'Invalid topic ID: '.$topicId );
		else{
			$rank	= $topic->rank + $direction;
			if( ( $next = $this->modelTopic->getByIndex( 'rank', $rank ) ) ){
				$this->modelTopic->edit( (int) $topicId, array( 'rank' => $rank ) );
				$this->modelTopic->edit( $next->topicId, array( 'rank' => $topic->rank ) );
			}
		}
		$this->restart( NULL, TRUE );
	}
	
	public function thread( $threadId ){
		$threadId	= (int) $threadId;
		$thread		= $this->modelThread->get( $threadId );
		if( !$thread ){
			$this->env->getMessenger()->noteError( 'Invalid thread ID: '.$threadId );
			$this->restart( NULL, TRUE );
		}
		$modelUser	= new Model_User( $this->env );
		$indices	= array(
			'threadId'	=> $threadId,
			'status'	=> array( 0, 1 ),
		);
		$posts	= $this->modelPost->getAllByIndices( $indices, array( 'createdAt' => 'ASC' ) );
		foreach( $posts as $nr => $post )
			$posts[$nr]->author	= $modelUser->get( $post->authorId );
		$topic	= $this->modelTopic->get( $thread->topicId );
		$this->addData( 'userId', $this->env->getSession()->get( 'userId' ) );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'topic', $topic );
		$this->addData( 'thread', $thread );
		$this->addData( 'posts', $posts );
	}

	public function topic( $topicId ){
		$topicId	= (int) $topicId;
		$topic		= $this->modelTopic->get( $topicId );
		if( !$topic ){
			$this->env->getMessenger()->noteError( 'Invalid topic ID: '.$topicId );
			$this->restart( NULL, TRUE );
		}
		$orders		= array( 'type' => 'DESC', 'modifiedAt' => 'DESC', 'createdAt' => 'DESC' );
		$threads	= $this->modelThread->getAll( array( 'topicId' => $topicId ), $orders );
		foreach( $threads as $nr => $thread ){
			$conditions	= array( 'threadId' => $thread->threadId, 'status' => array( 0, 1 ) );
			$thread->posts	= $this->modelPost->count( $conditions );
		}
		$topics		= $this->modelTopic->getAll( array(), array( 'rank' => 'ASC' ) );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'topics', $topics );
		$this->addData( 'topic', $topic );
		$this->addData( 'threads', $threads );
	}

	public function removePost( $postId ){
		$post		= $this->modelPost->get( $postId );
		if( !$post ){
			$this->env->getMessenger()->noteError( 'Invalid post ID: '.$postId );
			$this->restart( NULL, TRUE );
		}
		$userCanEdit	= in_array( 'editPost', $this->rights );
		$userIsManager	= in_array( 'removeTopic', $this->rights );
		$userOwnsPost	= $post->authorId === (int) $this->env->getSession()->get( 'userId' );
		if( !( $userCanEdit && $userOwnsPost || $userIsManager ) )
			$this->messenger->noteError( 'Access denied.' );
		else{
			$this->modelPost->edit( $postId, array( 'status' => -1 ) );
			$conditions	= array( 'threadId' => $post->threadId, 'status' => array( 0, 1 ) );
			if( !$this->modelPost->count( $conditions ) ){
				$thread	= $this->modelThread->get( $post->threadId );
				$this->modelPost->removeByIndex( 'threadId', $post->threadId );
				$this->modelThread->remove( $post->threadId );
				$this->messenger->noteSuccess( 'Removed post and thread "%s".', $thread->title );
				$this->restart( 'topic/'.$thread->topicId, TRUE );
			}
			else
				$this->messenger->noteSuccess( 'Removed post.' );
		}
		$this->restart( 'thread/'.$post->threadId, TRUE );
	}

	public function removeThread( $threadId ){
		$thread		= $this->modelThread->get( (int) $threadId );
		if( !$thread ){
			$this->env->getMessenger()->noteError( 'Invalid thread ID: '.$thread );
			$this->restart( NULL, TRUE );
		}
		$this->modelPost->removeByIndex( 'threadId', (int) $threadId );
		$this->modelThread->remove( (int) $threadId );
		$this->restart( 'topic/'.$thread->topicId, TRUE );
	}

	public function removeTopic( $topicId ){
		$topic		= $this->modelTopic->get( $topicId );
		if( !$topic ){
			$this->env->getMessenger()->noteError( 'Invalid topic ID: '.$topicId );
			$this->restart( NULL, TRUE );
		}
		if( $this->modelThread->countByIndex( 'topicId', $topicId ) ){
			$this->env->getMessenger()->noteError( 'Topic is not empty' );
			$this->restart( NULL, TRUE );
		}
		$this->modelTopic->remove( $topicId );
		$this->restart( NULL, TRUE );
	}
}
?>
