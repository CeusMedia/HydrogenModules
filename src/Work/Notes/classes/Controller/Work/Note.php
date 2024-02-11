<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Work_Note extends Controller
{
	protected Dictionary $session;
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Logic_Note $logic;

	/**
	 * @return void
	 * @throws ReflectionException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		$model		= new Model_Note( $this->env );
		$words		= (object) $this->getWords( 'add' );

		if( $this->request->getMethod()->isPost() && $this->request->has( 'save' ) ){
			$post	= $this->request->getAllFromSource( 'POST', TRUE );
			$data	= [
				'userId'		=> $this->session->get( 'auth_user_id' ),
				'projectId'		=> $post->get( 'note_projectId' ),
				'status'		=> '0',
				'title'			=> $post->get( 'note_title' ),
				'public'		=> (int) $post->get( 'note_public' ),
				'format'		=> $post->get( 'note_format' ),
				'content'		=> $post->get( 'note_content' ),
				'numberViews'	=> 0,
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			];
			if( !strlen( trim( $data['title'] ) ) )
				$this->messenger->noteError( $words->msgNoTitle );
#			if( !strlen( trim( $data['content'] ) ) )
#				$this->messenger->noteError( $words->msgNoContent );
			if( !$this->messenger->gotError() ){
				$noteId	= $model->add( $data, FALSE );
				$this->messenger->noteSuccess( $words->msgSuccess );
				if( trim( $post->get( 'tags' ) ) ){
					$tags	= explode( ' ', trim( $post->get( 'tags' ) ) );
					foreach( $tags as $tag ){
						$tagId	= $this->logic->createTag( $tag, FALSE );
						$this->logic->addTagToNote( $tagId, $noteId );
					}
					$this->messenger->noteSuccess( $words->msgTagsAdded, implode( ', ', $tags ) );
				}
				if( trim( $post->get( 'link_url' ) ) ){
					$linkId	= $this->logic->createLink( $post->get( 'link_url' ), FALSE );
					$this->logic->addLinkToNote( $linkId, $noteId, $post->get( 'link_title' ) );
				}
				$this->restart( './work/note/edit/'.$noteId );
			}
		}

		$note	= (object) [];
		$columns	= array_merge( $model->getColumns(), ['tags', 'link_url', 'link_title'] );
		foreach( $columns as $column )
			$note->$column	= $this->request->get( $column );
		$this->addData( 'note', $note );

		$projects	= [];
		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$logic		= Logic_Project::getInstance( $this->env );
			$userId		= $this->session->get( 'auth_user_id' );
			$projects	= $logic->getUserProjects( $userId, FALSE );
		}
		$this->addData( 'projects', $projects );
	}

	/**
	 * @param $noteId
	 * @param $tagId
	 * @return void
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function addLink( $noteId, $tagId = NULL ): void
	{
		if( (int) $tagId < 1 )
			$linkId	= $this->logic->createLink( $this->request->get( 'link_url' ), FALSE );
		$this->logic->addLinkToNote( $linkId, $noteId, $this->request->get( 'link_title' ), FALSE );
		$words		= (object) $this->getWords( 'msg' );
		$this->messenger->noteSuccess( $words->successNoteLinkAdded );
		$this->restart( './work/note/edit/'.$noteId );
	}

	/**
	 * @param string $tagId
	 * @param int $page
	 * @return void
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function addSearchTag( string $tagId, int $page = 0 ): void
	{
		$tags		= $this->session->get( 'filter_notes_tags' );
		$model		= new Model_Tag( $this->env );
		$tag		= $model->get( $tagId );
		if( !$tag )
			$this->messenger->noteError( 'invalid tag ID: '.$tagId );
		else{
			foreach( $tags as $item )
				if( $item->tagId == $tag->tagId )
					$this->restart( './work/note' );
			$tags[$tag->tagId]	= $tag;
			$this->session->set( 'filter_notes_tags', $tags );
			$this->session->set( 'filter_notes_offset', 0 );
		}
		$this->restart( './work/note/'.$page );
	}

	/**
	 * @param string $noteId
	 * @param string|NULL $tagId
	 * @return void
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function addTag( string $noteId, ?string $tagId = NULL ): void
	{
		$words			= (object) $this->getWords( 'msg' );
		if( !is_null( $tagId ) ){
			$this->logic->addTagToNote( $tagId, $noteId, Model_Note_Tag::STATUS_NORMAL, FALSE );
			$this->messenger->noteSuccess( $words->successNoteTagAdded );
		}
		else{
			if( ( $parts = explode( ' ', trim( $this->request->get( 'tag_content' ) ) ) ) ){
				foreach( $parts as $part ){
					$tagId	= $this->logic->createTag( $part, FALSE );
					$this->logic->addTagToNote( $tagId, $noteId, Model_Note_Tag::STATUS_NORMAL, FALSE );
				}
				$this->messenger->noteSuccess( $words->successNoteTagAdded );
			}
		}
		$this->restart( './work/note/edit/'.$noteId );
	}

	/**
	 * @param string $noteId
	 * @return void
	 * @throws ReflectionException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( string $noteId ): void
	{
		$words			= (object) $this->getWords( 'edit' );

		$modelNote		= new Model_Note( $this->env );
		$note			= $modelNote->get( $noteId );
		if( !$note ){
			$this->messenger->noteError( 'Invalid Note ID');
			$this->restart( './work/note/' );
		}

		if( $this->request->has( 'save' ) ){
			$data		= array(
				'projectId'		=> $this->request->get( 'note_projectId' ),
				'title'			=> $this->request->get( 'note_title' ),
				'content'		=> $this->request->get( 'note_content' ),
				'public'		=> (int) $this->request->get( 'note_public' ),
				'format'		=> $this->request->get( 'note_format' ),
				'modifiedAt'	=> time(),
			);
			if( !strlen( trim( $data['title'] ) ) )
				$this->messenger->noteError( $words->msgNoTitle );
			if( !$this->messenger->gotError() ){
				if( $modelNote->edit( $noteId, $data, FALSE ) )
					$this->messenger->noteSuccess( $words->msgSuccess, $data['title'] );
				else
					$this->messenger->noteNotice( $words->msgNoChanges, $data['title'] );
				$this->restart( './work/note/view/'.$noteId );
			}
		}
		$this->addData( 'note', $this->logic->getNoteData( $noteId ) );
		$this->addData( 'relatedTags', $this->logic->getRelatedTags( $noteId ) );

		$projects	= [];
		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$logic		= Logic_Project::getInstance( $this->env );
			$userId		= $this->session->get( 'auth_user_id' );
			$projects	= $logic->getUserProjects( $userId, FALSE );
		}
		$this->addData( 'projects', $projects );
	}

	/**
	 * @param $reset
	 * @return void
	 */
	public function filter( $reset = NULL ): void
	{
		if( $reset ){
			$this->session->remove( 'filter_notes_visibility' );
			$this->session->remove( 'filter_notes_author' );
			$this->session->remove( 'filter_notes_projectId' );
			$this->session->remove( 'filter_notes_tags' );
			$this->session->remove( 'filter_notes_limit' );
			$this->session->remove( 'filter_notes_order' );
		}
		if( $this->request->has( 'filter_visibility' ) )
			$this->session->set( 'filter_notes_visibility', $this->request->get( 'filter_visibility' ) );
		if( $this->request->has( 'filter_author' ) )
			$this->session->set( 'filter_notes_author', $this->request->get( 'filter_author' ) );
//		if( $this->request->has( 'filter_public' ) )
//			$this->session->set( 'filter_notes_public', $this->request->get( 'filter_public' ) );
		if( $this->request->has( 'filter_projectId' ) )
			$this->session->set( 'filter_notes_projectId', $this->request->get( 'filter_projectId' ) );
		if( $this->request->has( 'filter_order' ) )
			$this->session->set( 'filter_notes_order', $this->request->get( 'filter_order' ) );
		if( $this->request->has( 'filter_limit' ) )
			$this->session->set( 'filter_notes_limit', $this->request->get( 'filter_limit' ) );
		if( $this->request->has( 'filter_query' ) )
			$this->session->set( 'filter_notes_term', $this->request->get( 'filter_query' ) );

/*		//  strip found tags from query, disabled for now
		if( $this->request->has( 'filter_query' ) ){
			if( trim( $query = $this->request->get( 'filter_query' ) ) ){
				$tags		= $this->session->get( 'filter_notes_tags' );
				if( !is_array( $tags ) )
					$tags	= [];
				$modelTag	= new Model_Tag( $this->env );
				$parts		= explode( ' ', $query );																			//  split query into parts
				foreach( $parts as $nr => $part ){																				//  iterate query parts
					$query	= 'SELECT * FROM '.$modelTag->getName().' WHERE content LIKE "'.$part.'"';							//  try to find tag (case-insensitive)
					$result	= $this->env->getDatabase()->query( $query );														//  in database tags
					foreach( $result->fetchAll( PDO::FETCH_OBJ ) as $tag ){														//  iterate results
						unset( $parts[$nr] );																					//  remove part from query
						if( !array_key_exists( $tag->tagId, $tags ) )															//  tag not yet in tag list
							$tags[$tag->tagId]	= $tag;																			//  enlist tag in tag list
					}
				}
				$query	= implode( ' ', $parts );																				//  combine parts to clean query
				$this->session->set( 'filter_notes_tags', $tags );																	//  store tag list in session
			}
			$this->session->set( 'filter_notes_term', $query );																		//  store query in session
		}*/
		$this->restart( NULL, TRUE );
	}

	/**
	 * @param string $tagId
	 * @param int $page
	 * @return void
	 */
	public function forgetTag( string $tagId, int $page = 0 ): void
	{
		$list		= [];
		$tags		= $this->session->get( 'filter_notes_tags' );
		foreach( $tags as $tag )
			if( $tag->tagId != $tagId )
				$list[]	= $tag;
		$this->session->set( 'filter_notes_tags', $list );
		$this->session->set( 'filter_notes_offset', 0 );
		$this->restart( './work/note/'.$page );
	}

	/**
	 * @param string $noteId
	 * @param string $tagId
	 * @return void
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function ignoreTag( string $noteId, string $tagId ): void
	{
		$this->logic->ignoreTagOnNote( $tagId, $noteId );
		$this->restart( './work/note/edit/'.$noteId );
	}

	/**
	 * @param int $page
	 * @return void
	 * @throws ReflectionException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( int $page = 0 ): void
	{
		$tags				= $this->session->get( 'filter_notes_tags' );
		$query				= $this->session->get( 'filter_notes_term');
		$filterOrder		= $this->session->get( 'filter_notes_order' );
		$filterDirection	= $this->session->get( 'filter_notes_direction' );
		$filterLimit		= $this->session->get( 'filter_notes_limit' );
		$filterAuthor		= $this->session->get( 'filter_notes_author' );
		$filterPublic		= $this->session->get( 'filter_notes_public' );
		$filterProjectId	= $this->session->get( 'filter_notes_projectId' );
		$visibility			= $this->session->get( 'filter_notes_visibility' );
		$filterLimit		= $filterLimit ?: 10;
		if( !$filterOrder )
			$filterOrder		= 'modifiedAt';
		if( !$filterDirection )
			$filterDirection	= 'DESC';

		$filterOffset	= $page * $filterLimit;
		if( $this->request->has( 'offset' ) )
			$this->session->set( 'filter_notes_offset', (int) $filterOffset );

		if( !is_array( $tags ) )
			$tags	= [];

		$userId		= $this->session->get( 'auth_user_id' );
		$projects	= [];
		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$logic		= Logic_Project::getInstance( $this->env );
			$projects	= $logic->getUserProjects( $userId );
		}
		$notes		= [];
		$conditions	= [];
		$orders		= [$filterOrder => $filterDirection];
//		if( $filterPublic > 0 )
//			$conditions['public']		= $filterPublic == 2 ? 0 : 1;
		if( $filterAuthor > 0 )
			$conditions['userId']		= $filterAuthor == 1 ? $userId : '!= '.$userId;
		if( strlen( trim( (string) $filterProjectId ) ) )
			$conditions['projectId']	= $filterProjectId;
		else if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$conditions['projectId']	= array_merge( [0], array_keys( $projects ) );
		}

		$offset	= $page * $filterLimit;
		$limits		= [$offset, $filterLimit];
		if( $query ){
			$notes	= $this->logic->searchNotes( $query, $conditions, $orders, $limits );
		}
		else{
			$notes	= $this->logic->getTopNotes( $conditions, $orders, $limits );
		}
//print_m( $notes );die;
		$modelUser	= new Model_User( $this->env );
		foreach( $notes['list'] as $nr => $note )
			$notes['list'][$nr]->user	= $modelUser->get( $note->userId );

		$this->addData( 'filterOffset', $filterOffset );
		$this->addData( 'filterLimit', $filterLimit );
		$this->addData( 'page', $page );
		$this->addData( 'filterVisibility', $visibility );
		$this->addData( 'filterAuthor', $filterAuthor );
		$this->addData( 'filterPublic', $filterPublic );
		$this->addData( 'filterProjectId', $filterProjectId );
		$this->addData( 'filterOrder', $filterOrder );
		$this->addData( 'projects', $projects );
		$this->addData( 'notes', $notes );
	}

	/**
	 * @param string $linkId
	 * @return void
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function link( string $linkId ): void
	{
		$model	= new Model_Link( $this->env );
		$link	= $model->get( $linkId );
		if( !$link ){
			$this->messenger->noteError( 'Invalid link ID' );
			$this->restart( NULL, TRUE );
		}
		header( 'Location: '.$link->url );
		exit;
	}

	/**
	 * @param string $noteId
	 * @return void
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $noteId ): void
	{
		$this->logic->removeNote( $noteId );
		$words		= (object) $this->getWords( 'msg' );
		$this->messenger->noteSuccess( $words->successNoteRemoved );
		$this->restart( './work/note' );
	}

	/**
	 * @param string $noteId
	 * @param string $tagId
	 * @return void
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeTag( string $noteId, string $tagId ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		$this->logic->removeTagFromNote( $tagId, $noteId );
		$this->messenger->noteSuccess( $words->successNoteTagRemoved );
		$this->restart( './work/note/edit/'.$noteId );
	}

	/**
	 * @param string $noteId
	 * @param string $noteLinkId
	 * @return void
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeLink( string $noteId, string $noteLinkId ): void
	{
		$this->logic->removeNoteLink( $noteLinkId );
		$words		= (object) $this->getWords( 'msg' );
		$this->messenger->noteSuccess( $words->successNoteLinkRemoved );
		$this->restart( './work/note/edit/'.$noteId );
	}

	/**
	 * @param string $noteId
	 * @return void
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( string $noteId ): void
	{
		$modelUser	= new Model_User( $this->env );
		$this->logic->countNoteView( $noteId );
		$note		= $this->logic->getNoteData( $noteId );
		$note->user	= $modelUser->get( $note->userId );
		if( !$note ){
			$this->messenger->noteError( 'Invalid Note ID');
			$this->restart( './work/note/' );
		}
		$this->addData( 'note', $note );
	}

	/**
	 * @return void
	 * @throws ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logic		= Logic_Note::getInstance( $this->env );
		$this->logic->setContext(
			$this->session->get( 'auth_user_id', '0' ),
			$this->session->get( 'auth_role_id', '0' ),
			$this->session->get( 'filter_notes_projectId', '' )
		);
		$this->addData( 'logicNote', $this->logic );
	}
}
