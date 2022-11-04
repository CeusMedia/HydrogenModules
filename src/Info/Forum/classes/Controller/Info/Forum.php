<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\Image;
use CeusMedia\Common\UI\Image\Processing as ImageProcessing;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Info_Forum extends Controller
{
	/**	@var	Model_Forum_Post		$modelPost */
	protected $modelPost;

	/**	@var	Model_Forum_Thread		$modelThread */
	protected $modelThread;

	/**	@var	Model_Forum_Topic		$modelTopic */
	protected $modelTopic;

	/**	@var	array					$rights */
	protected $rights;

	/**	@var	Dictionary				$options */
	protected $ptions;

	public function addPost( $threadId )
	{
		$words		= (object) $this->getWords( 'msg' );

		$thread		= $this->modelThread->get( $threadId );
		if( !$thread ){
			$this->messenger->noteError( $words->errorInvalidThreadId, $threadId );
			$this->restart( NULL, TRUE );
		}
		if( $thread->status == 2 ){
			$this->messenger->noteError( $words->errorThreadClosed, $thread->title );
			$this->restart( NULL, TRUE );
		}

		$data		= $this->request->getAll();
		if( in_array( 'approvePost', $this->rights ) )
			$data['status']	= 1;
		$data['threadId']	= $threadId;
		$data['authorId']	= $this->env->getSession()->get( 'auth_user_id' );
		$data['createdAt']	= time();
		$data['type']		= 0;

		if( isset( $data['file'] ) && $data['file']['error'] !== 4 ){
			$file	= (object) $data['file'];
			$config	= $this->env->getConfig()->getAll( 'module.info_forum.upload.', TRUE );
			if( $file->error ){
				$key	= "errorUpload".$file->error;
				$this->messenger->noteError( $words->$key );
				$this->restart( 'thread/'.$threadId, TRUE );
			}
			$path		= $config->get( 'path' );
			$fileName	= uniqid().".".pathinfo( $file->name, PATHINFO_EXTENSION );
			try{
				move_uploaded_file( $file->tmp_name, $path.$fileName );
				$image		= new Image( $path.$fileName );
				$processor	= new ImageProcessing( $image );
				$processor->scaleDownToLimit( $config->get( 'max.x' ), $config->get( 'max.y' ) );
				$image->save();
			}
			catch( Exception $e ){
				$this->messenger->noteError( $words->errorImageInvalid.': '.$e->getMessage() );
				@unlink( $path.$fileName );
				$this->restart( 'thread/'.$threadId, TRUE );
			}
			$data['content'] = $fileName."\n".$this->request->get( 'title' );
			$data['type']	= 1;
		}

		$postId	= $this->modelPost->add( $data, FALSE );
		$this->messenger->noteSuccess( $words->successPostAdded, $postId );
		$this->modelThread->edit( $threadId, array( 'modifiedAt' => time() ) );
		$this->modelTopic->edit( $thread->topicId, array( 'modifiedAt' => time() ) );
		$this->cache->remove( 'info.forum.userPosts' );
		$this->informThreadUsersAboutPost( $threadId, $postId );
		$this->restart( 'thread/'.$threadId.'#post-'.$postId, TRUE );
	}

	public function addThread()
	{
		$words		= (object) $this->getWords( 'msg' );
		$data		= $this->request->getAll();
		$data['authorId']	= $this->env->getSession()->get( 'auth_user_id' );
		$data['createdAt']	= time();
		$threadId	= $this->modelThread->add( $data, FALSE );
		$thread		= $this->modelThread->get( $threadId );
		$this->messenger->noteSuccess( $words->successThreadAdded, $data['title'] );
		$this->request->set( 'threadId', $threadId );
		$this->restart( 'addPost/'.$threadId, TRUE );
	}

	public function addTopic()
	{
		$words		= (object) $this->getWords( 'msg' );
		$data		= $this->request->getAll();
		$data['authorId']	= $this->env->getSession()->get( 'auth_user_id' );
		$data['rank']		= $this->modelTopic->count();
		$data['createdAt']	= time();
		$postId		= $this->modelTopic->add( $data );
		$this->messenger->noteSuccess( $words->successTopicAdded, $data['title'] );
		$this->restart( NULL, TRUE );
	}

	public function approvePost( $postId )
	{
		$post		= $this->modelPost->get( $postId );
		$words		= (object) $this->getWords( 'msg' );
		if( !$post ){
			$this->messenger->noteError( $words->errorInvalidPostId, $postId );
			$this->restart( NULL, TRUE );
		}
		$this->modelPost->edit( $postId, ['status' => 1] );
		$this->restart( 'thread/'.$post->threadId, TRUE );
	}

	public function index()
	{
		$topics		= $this->modelTopic->getAll( [], ['rank' => 'ASC'] );
//		if( count( $topics ) == 1 )
//			$this->restart( './info/forum/topic/'.$topics[0]->topicId );
		foreach( $topics as $nr => $topic ){
			$threads	= $this->modelThread->getAllByIndex( 'topicId', $topic->topicId );
			$topic->threads	= count( $threads );
			$topic->posts	= 0;
			$threadIds	= [];
			foreach( $threads as $thread )
				$threadIds[]	= $thread->threadId;
			if( $threadIds )
				$topic->posts	+= $this->modelPost->countByIndex( 'threadId', $threadIds );
		}
		$this->addData( 'rights', $this->rights );
		$this->addData( 'topics', $topics );
	}

	public function rankTopic( $topicId, $downwards = NULL )
	{
		$words		= (object) $this->getWords( 'msg' );
		$direction	= (boolean) $downwards ? +1 : -1;
		if( !( $topic = $this->modelTopic->get( (int) $topicId ) ) )
			$this->messenger->noteError( $words->errorInvalidTopicId, $topicId );
		else{
			$rank	= $topic->rank + $direction;
			if( ( $next = $this->modelTopic->getByIndex( 'rank', $rank ) ) ){
				$this->modelTopic->edit( (int) $topicId, ['rank' => $rank] );
				$this->modelTopic->edit( $next->topicId, ['rank' => $topic->rank] );
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function thread( $threadId )
	{
		if( $this->env->getRequest()->has( 'mail' ) )
			$this->informThreadUsersAboutPost( $threadId, 71 );
		$words		= (object) $this->getWords( 'msg' );
		$threadId	= (int) $threadId;
		$thread		= $this->modelThread->get( $threadId );
		if( !$thread ){
			$this->messenger->noteError( $words->errorInvalidThreadId, $threadId );
			$this->restart( NULL, TRUE );
		}
		$modelUser	= new Model_User( $this->env );
		$indices	= array(
			'threadId'	=> $threadId,
			'status'	=> [0, 1],
		);
		$posts	= $this->modelPost->getAllByIndices( $indices, ['createdAt' => 'ASC'] );
		foreach( $posts as $nr => $post )
			$posts[$nr]->author	= $modelUser->get( $post->authorId );
		$topic	= $this->modelTopic->get( $thread->topicId );
		$this->addData( 'userId', $this->userId );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'topic', $topic );
		$this->addData( 'thread', $thread );
		$this->addData( 'posts', $posts );
		$this->addData( 'userPosts', $this->userPosts );
	}

	public function topic( $topicId )
	{
		$topicId	= (int) $topicId;
		$topic		= $this->modelTopic->get( $topicId );
		$words		= (object) $this->getWords( 'msg' );
		if( !$topic ){
			$this->messenger->noteError( $words->errorInvalidTopicId, $topicId );
			$this->restart( NULL, TRUE );
		}
		$orders		= ['type' => 'DESC', 'modifiedAt' => 'DESC', 'createdAt' => 'DESC'];
		$threads	= $this->modelThread->getAll( ['topicId' => $topicId], $orders );
		foreach( $threads as $nr => $thread ){
			$conditions	= ['threadId' => $thread->threadId, 'status' => [0, 1]];
			$thread->posts	= $this->modelPost->count( $conditions );
		}
		$topics		= $this->modelTopic->getAll( [], ['rank' => 'ASC'] );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'userId', $this->userId );
		$this->addData( 'topics', $topics );
		$this->addData( 'topic', $topic );
		$this->addData( 'threads', $threads );
	}

	public function removePost( $postId )
	{
		$post		= $this->modelPost->get( $postId );
		$words		= (object) $this->getWords( 'msg' );
		if( !$post ){
			$this->messenger->noteError( $words->errorInvalidPostId, $postId );
			$this->restart( NULL, TRUE );
		}
		$userCanEdit	= in_array( 'editPost', $this->rights );
		$userIsManager	= in_array( 'removeTopic', $this->rights );
		$userOwnsPost	= $post->authorId === (int) $this->env->getSession()->get( 'auth_user_id' );
		if( !( $userCanEdit && $userOwnsPost || $userIsManager ) )
			$this->messenger->noteError( $words->errorAccessDenied );
		else{
			$this->modelPost->remove( $postId );
			if( !$this->modelPost->count( ['threadId' => $post->threadId] ) ){
				$thread	= $this->modelThread->get( $post->threadId );
				$this->modelPost->removeByIndex( 'threadId', $post->threadId );
				$this->modelThread->remove( $post->threadId );
				$this->restart( 'topic/'.$thread->topicId, TRUE );
			}
			$this->messenger->noteSuccess( $words->successPostRemoved );
			if( $post->type == 1 ){
				@unlink( "contents/forum/".$post->content );
			}
			$this->cache->remove( 'info.forum.userPosts' );
		}
		$this->restart( 'thread/'.$post->threadId, TRUE );
	}

	public function removeThread( $threadId )
	{
		$thread		= $this->modelThread->get( (int) $threadId );
		$words		= (object) $this->getWords( 'msg' );
		if( !$thread ){
			$this->messenger->noteError( $words->errorInvalidThreadId, $threadId );
			$this->restart( NULL, TRUE );
		}
		$this->modelPost->removeByIndex( 'threadId', (int) $threadId );
		$this->modelThread->remove( (int) $threadId );
		$this->messenger->noteSuccess( $words->successThreadRemoved, $thread->title );
		$this->restart( 'topic/'.$thread->topicId, TRUE );
	}

	public function removeTopic( $topicId )
	{
		$topic		= $this->modelTopic->get( $topicId );
		$words		= (object) $this->getWords( 'msg' );
		if( !$topic ){
			$this->messenger->noteError( $words->errorInvalidTopicId, $topicId );
			$this->restart( NULL, TRUE );
		}
		if( $this->modelThread->countByIndex( 'topicId', $topicId ) ){
			$this->messenger->noteError( $words->errorTopicNotEmpty, $topic->title );
			$this->restart( NULL, TRUE );
		}
		$this->modelTopic->remove( $topicId );
		$this->messenger->noteSuccess( $words->successTopicRemoved, $topic->title );
		$this->restart( NULL, TRUE );
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->request		= $this->env->getRequest();
		$this->modelPost	= new Model_Forum_Post( $this->env );
		$this->modelThread	= new Model_Forum_Thread( $this->env );
		$this->modelTopic	= new Model_Forum_Topic( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->options		= $this->env->getConfig()->getAll( 'module.info_forum.', TRUE );
		$this->rights		= $this->env->getAcl()->index( 'info/forum' );
		$this->userId		= $this->env->getSession()->get( 'auth_user_id' );
		$this->cache		= $this->env->getCache();

		if( !( $this->userPosts = $this->cache->get( 'info.forum.userPosts' ) ) ){
			$model	= new Model_User( $this->env );
			$this->userPosts		= [];
			foreach( $model->getAll() as $user )
				$this->userPosts[$user->userId]	= $this->modelPost->countByIndex( 'authorId', $user->userId );
			$this->cache->set( 'info.forum.userPosts', $this->userPosts );
		}

		$path	= $this->env->getConfig()->get( 'module.info_forum.upload.path' );
		if( !file_exists( $path ) )
			mkdir( $path, 0770, TRUE );
	}

	protected function informThreadUsersAboutPost( $threadId, $postId = NULL )
	{
		$logicMail	= Logic_Mail::getInstance( $this->env );
		if( !( $thread = $this->modelThread->get( $threadId ) ) )
			throw new InvalidArgumentException( 'Invalid thread ID' );
		if( !( $post = $this->modelPost->get( (int) $postId ) ) )
			throw new InvalidArgumentException( 'Invalid post ID' );
		$authors	= [];
		$modelUser	= new Model_User( $this->env );
		$posts		= $this->modelPost->getAllByIndex( 'threadId', $threadId, ['postId' => 'ASC'] );
		foreach( $posts as $entry )
			if( !array_key_exists( $entry->authorId, $authors ) )
				$authors[$entry->authorId]	= $modelUser->get( $entry->authorId );

		$useSettings	= $this->env->getModules()->has( 'Manage_My_User_Settings' );			//  user settings are enabled
		$config	= $this->env->getConfig();
		foreach( $authors as $authorId => $author ){
			if( $useSettings )
				$config		= Model_User_Setting::applyConfigStatic( $this->env, $authorId );
			if( !$config->get( 'module.info_forum.mail.inform.authors' ) )
				continue;
			if( $author->userId == $post->authorId )
				continue;
			$data		= array(
				'user'		=> $author,
				'config'	=> $config,
				'options'	=> $this->options,
				'owner'		=> $modelUser->get( $thread->authorId ),
				'author'	=> $modelUser->get( $post->authorId ),
				'thread'	=> $thread,
				'post'		=> $post,
				'posts'		=> $posts,
				'authors'	=> $authors,
			);
			$mail	= new Mail_Forum_Answer( $this->env, $data );
			if( $this->options->get( 'mail.sender' ) )
				$mail->setSender( $this->options->get( 'mail.sender' ) );
			$language	= $this->env->getLanguage()->getLanguage();
			$logicMail->handleMail( $mail, $author, $language );
		}
	}
}
