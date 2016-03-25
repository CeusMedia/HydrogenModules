<?php
class Controller_Info_Blog extends CMF_Hydrogen_Controller{

	protected $modelPost;
	protected $modelUser;
	protected $messenger;

	protected function __onInit(){
		$this->modelPost	= new Model_Blog_Post( $this->env );
		$this->modelUser	= new Model_User( $this->env );
		$this->messenger	= $this->env->getMessenger();
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
	}

	public function post( $postId = NULL ){
		if( !$postId )
			$this->restart( NULL, TRUE );
		$post	= $this->modelPost->get( (int) $postId );
		if( !$post ){
			$this->messenger->noteError( 'Invalid post ID.' );
			$this->restart( NULL, TRUE );
		}
		$post->author	= $this->modelUser->get( $post->authorId );
		$this->modelPost->edit( $post->postId, array(
			'viewedAt'	=> time(),
			'nrViews'	=> $post->nrViews + 1,
		) );
		$this->addData( 'post', $post );
	}
}
