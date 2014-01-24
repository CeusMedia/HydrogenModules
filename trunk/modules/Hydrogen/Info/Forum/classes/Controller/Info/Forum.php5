<?php
class Controller_Info_Forum extends CMF_Hydrogen_Controller{

	protected $modelPost;
	protected $modelThread;
	protected $modelTopic;

	public function __onInit(){
		$this->modelPost	= new Model_Forum_Post( $this->env );
		$this->modelThread	= new Model_Forum_Thread( $this->env );
		$this->modelTopic	= new Model_Forum_Topic( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->options		= $this->env->getConfig()->getAll( 'module.info_forum.' );
		$this->rights		= $this->env->getAcl()->index( 'info/forum' );
	}

	public function index( $threadId = NULL ){
	//	$topics	= $this->modelTopic()->getAll( array(), array( 'title' => 'ASC' ) );
		
		$threads	= $this->modelThread->getAll( array(), array( 'modifiedAt' => 'DESC', 'createdAt' => 'DESC' ) );
		foreach( $threads as $nr => $thread ){
			$thread->post	= $this->modelPost->countByIndex( 'threadId', $thread->threadId );
		}
		$this->addData( 'threads', $threads );
		$this->addData( 'rights', $this->rights );
	}

	public function thread( $threadId ){
		$thread		= $this->modelThread->get( $threadId );
		if( !$thread ){
			$this->env->getMessenger()->noteError( 'Invalid thread ID: '.$threadId );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'thread', $thread );
		$this->addData( 'posts', $this->modelPost->getAllByIndex( 'threadId', $threadId ) );
		$this->addData( 'rights', $this->rights );
	}

	public function addThread(){
		$request	= $this->env->getRequest();
		$data		= $request->getAll();
		$data['createdAt']	= time();
		$data['authorId']	= $this->env->getSession()->get( 'userId' );
		$this->modelThread->add( $data );
		$this->restart( NULL, TRUE );
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
		$data['threadId']	= $threadId;
		$data['authorId']	= $this->env->getSession()->get( 'userId' );
		$data['createdAt']	= time();
		$this->modelPost->add( $data );
		$this->restart( './info/forum/thread/'.$threadId );
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

	public function removePost( $postId ){
		$post		= $this->modelPost->get( $postId );
		if( !$post ){
			$this->env->getMessenger()->noteError( 'Invalid post ID: '.$postId );
			$this->restart( NULL, TRUE );
		}
		$this->modelPost->edit( $postId, array( 'status' => -1 ) );
		$this->restart( './info/forum/thread/'.$post->threadId );
	}
}
?>
