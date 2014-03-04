<?php
class Controller_Work_Note extends CMF_Hydrogen_Controller{

	public function add(){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$messenger	= $this->env->getMessenger();
		$logic		= new Logic_Note( $this->env );
		$model		= new Model_Note( $this->env );
		$words		= (object) $this->getWords( 'add' );
		$data		= $request->getAllFromSource( 'post' )->getAll();

		if( $request->has( 'save' ) ){
			$data		= array(
				'userId'	=> $session->get( 'userId' ),
				'projectId'	=> $request->get( 'note_projectId' ),
				'title'		=> $request->get( 'note_title' ),
				'public'	=> (int) $request->get( 'note_public' ),
				'format'	=> $request->get( 'note_format' ),
				'content'	=> $request->get( 'note_content' ),
				'createdAt'	=> time(),
			);
			if( !strlen( trim( $data['title'] ) ) )
				$messenger->noteError( $words->msgNoTitle );
#			if( !strlen( trim( $data['content'] ) ) )
#				$messenger->noteError( $words->msgNoContent );
			if( !$messenger->gotError() ){
				$noteId	= $model->add( $data, FALSE );
				$messenger->noteSuccess( $words->msgSuccess );
				if( trim( $request->get( 'tags' ) ) ){
					$tags	= explode( ' ', trim( $request->get( 'tags' ) ) );
					foreach( $tags as $tag ){
						$tagId	= $logic->createTag( $tag, FALSE );
						$logic->addTagToNote( $tagId, $noteId );
					}
					$messenger->noteSuccess( $words->msgTagsAdded, implode( ', ', $tags ) );
				}
				if( trim( $request->get( 'link_url' ) ) ){
					$linkId	= $logic->createLink( $request->get( 'link_url' ), FALSE );
					$logic->addLinkToNote( $linkId, $noteId, $request->get( 'link_title' ) );
				}
				$this->restart( './work/note/edit/'.$noteId );
			}
		}

		$note	= (object) array();
		$columns	= array_merge( $model->getColumns(), array( 'tags', 'link_url', 'link_title' ) );
		foreach( $columns as $column )
			$note->$column	= $request->get( $column );
		$this->addData( 'note', $note );

		$projects	= array();
		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$logic		= new Logic_Project( $this->env );
			$userId		= $this->env->getSession()->get( 'userId' );
			$projects	= $logic->getUserProjects( $userId, FALSE );
		}
		$this->addData( 'projects', $projects );
	}

	public function addLink( $noteId, $tagId = NULL ){
		$request			= $this->env->getRequest();
		$logic				= new Logic_Note( $this->env );
		if( (int) $tagId < 1 )
			$linkId	= $logic->createLink( $request->get( 'link_url' ), FALSE );
		$logic->addLinkToNote( $linkId, $noteId, $request->get( 'link_title' ), FALSE );
		$words		= (object) $this->getWords( 'msg' );
		$this->env->getMessenger()->noteSuccess( $words->successNoteLinkAdded );
		$this->restart( './work/note/edit/'.$noteId );
	}

	public function addSearchTag( $tagId, $page = 0 ){
		$session	= $this->env->getSession();
		$tags		= $session->get( 'filter_notes_tags' );
		$model		= new Model_Tag( $this->env );
		$tag		= $model->get( $tagId );
		if( !$tag )
			$this->env->getMessenger()->noteError( 'invalid tag ID: '.$tagId );
		else{
			foreach( $tags as $item )
				if( $item->tagId == $tag->tagId )
					$this->restart( './work/note' );
			$tags[]	= $tag;
			$session->set( 'filter_notes_tags', $tags );
			$session->set( 'filter_notes_offset', 0 );
		}
		$this->restart( './work/note/'.$page );
	}

	public function addTag( $noteId, $tagId = NULL ){
		$request			= $this->env->getRequest();
		$logic				= new Logic_Note( $this->env );
		if( (int) $tagId < 1 ){
			$tag	= $request->get( 'tag_content' );
			if( !strlen( trim( $tag ) ) )
				$this->restart( './work/note/edit/'.$noteId );
			$tagId	= $logic->createTag( $tag, FALSE );
		}
		$logic->addTagToNote( $tagId, $noteId, FALSE );
		$words		= (object) $this->getWords( 'msg' );
		$this->env->getMessenger()->noteSuccess( $words->successNoteTagAdded );
		$this->restart( './work/note/edit/'.$noteId );
	}

	public function edit( $noteId ){
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$logic			= new Logic_Note( $this->env );
		$words			= (object) $this->getWords( 'edit' );

		$modelNote		= new Model_Note( $this->env );
		$note			= $modelNote->get( $noteId );
		if( !$note ){
			$messenger->noteError( 'Invalid Note ID');
			$this->restart( './work/note/' );
		}

		if( $request->has( 'save' ) ){
			$data		= array(
				'projectId'		=> $request->get( 'note_projectId' ),
				'title'			=> $request->get( 'note_title' ),
				'content'		=> $request->get( 'note_content' ),
				'public'		=> (int) $request->get( 'note_public' ),
				'format'		=> $request->get( 'note_format' ),
				'modifiedAt'	=> time(),
			);
			if( !strlen( trim( $data['title'] ) ) )
				$messenger->noteError( $words->msgNoTitle );
			if( !$messenger->gotError() ){
				if( $modelNote->edit( $noteId, $data, FALSE ) )
					$messenger->noteSuccess( $words->msgSuccess, $data['title'] );
				else
					$messenger->noteNotice( $words->msgNoChanges, $data['title'] );
				$this->restart( './work/note/view/'.$noteId );
			}
		}
		$this->addData( 'note', $logic->getNoteData( $noteId ) );
		$this->addData( 'relatedTags', $logic->getRelatedTags( $noteId ) );

		$projects	= array();
		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$logic		= new Logic_Project( $this->env );
			$userId		= $this->env->getSession()->get( 'userId' );
			$projects	= $logic->getUserProjects( $userId, FALSE );
		}
		$this->addData( 'projects', $projects );
	}

	public function filter(){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();

		if( $request->has( 'filter_visibility' ) )
			$session->set( 'filter_notes_visibility', $request->get( 'filter_visibility' ) );
		if( $request->has( 'filter_author' ) )
			$session->set( 'filter_notes_author', $request->get( 'filter_author' ) );
		if( $request->has( 'filter_public' ) )
			$session->set( 'filter_notes_public', $request->get( 'filter_public' ) );
		if( $request->has( 'filter_projectId' ) )
			$session->set( 'filter_notes_projectId', $request->get( 'filter_projectId' ) );
		if( $request->has( 'filter_limit' ) )
			$session->set( 'filter_notes_limit', $request->get( 'filter_limit' ) );

		if( $request->has( 'filter_query' ) ){
			if( trim( $query = $request->get( 'filter_query' ) ) ){
				$tags		= $session->get( 'filter_notes_tags' );
				if( !is_array( $tags ) )
					$tags	= array();
				$modelTag	= new Model_Tag( $this->env );

				$parts	= explode( ' ', $query );
				$parts	= array_combine( $parts, $parts );
				$query	= 'SELECT * FROM '.$modelTag->getName().' WHERE content IN("'.implode( '", "', $parts ).'")';
				$result	= $this->env->getDatabase()->query( $query );
				foreach( $result->fetchAll( PDO::FETCH_OBJ ) as $tag ){
					unset( $parts[$tag->content] );
					if( !array_key_exists( $tag->tagId, $tags ) )
						$tags[$tag->tagId]	= $tag;
				}
				$query	= implode( ' ', $parts );
				$session->set( 'filter_notes_tags', $tags );
			}
			$session->set( 'filter_notes_term', $query );
		}
		$this->restart( NULL, TRUE );
	}

	public function forgetTag( $tagId, $page = 0 ){
		$session	= $this->env->getSession();
		$tags		= $session->get( 'filter_notes_tags' );
		foreach( $tags as $tag )
			if( $tag->tagId != $tagId )
				$list[]	= $tag;
		$session->set( 'filter_notes_tags', $list );
		$session->set( 'filter_notes_offset', 0 );
		$this->restart( './work/note/'.$page );
	}

	public function index( $page = 0 ){
		$request			= $this->env->getRequest();
		$session			= $this->env->getSession();
		$tags				= $session->get( 'filter_notes_tags' );
		$query				= $session->get( 'filter_notes_term');
		$order				= $session->get( 'filter_notes_order' );
		$direction			= $session->get( 'filter_notes_direction' );
		$limit				= $session->get( 'filter_notes_limit' );
		$filterAuthor		= $session->get( 'filter_notes_author' );
		$filterPublic		= $session->get( 'filter_notes_public' );
		$filterProjectId	= $session->get( 'filter_notes_projectId' );
		$visibility			= $session->get( 'filter_notes_visibility' );
		$limit				= $limit ? $limit  : 10;
		if( !$order || !$direction ){
			$order		= 'modifiedAt';
			$direction	= 'DESC';
		}

		if( $request->has( 'offset' ) )
			$session->set( 'filter_notes_offset', (int) $request->get( 'offset' ) );

		if( !is_array( $tags ) )
			$tags	= array();

		$logic		= new Logic_Note( $this->env );

		$logic->setContext(
			$session->get( 'userId' ),
			$session->get( 'roleId' ),
			$session->get( 'filter_projectId' )
		);

		$notes		= array();
		$conditions	= array();
		$userId		= $session->get( 'userId' );
		if( $filterPublic > 0 )
			$conditions['public']		= $filterPublic == 2 ? 0 : 1;
		if( $filterAuthor > 0 )
			$conditions['userId']		= $filterAuthor == 1 ? $userId : '!='.$userId;
		if( $filterProjectId )
			$conditions['projectId']	= $filterProjectId;
		$offset	= $page * $limit;
		if( $query || count( $tags ) ){
			$notes	= $logic->searchNotes( $query, $tags, $offset, $limit );
		}
		else{
			$notes	= $logic->getTopNotes( $conditions, $offset, $limit );
		}
		$modelUser	= new Model_User( $this->env );
		foreach( $notes['list'] as $nr => $note )
			$notes['list'][$nr]->user	= $modelUser->get( $note->userId );
		$logic		= new Logic_Project( $this->env );
		$projects	= $logic->getUserProjects( $userId );
		$this->addData( 'offset', $offset );
		$this->addData( 'limit', $limit );
		$this->addData( 'page', $page );
		$this->addData( 'filterVisibility', $visibility );
		$this->addData( 'filterAuthor', $filterAuthor );
		$this->addData( 'filterPublic', $filterPublic );
		$this->addData( 'filterProjectId', $filterProjectId );
		$this->addData( 'projects', $projects );
		$this->addData( 'notes', $notes );
	}

	public function link( $linkId ){
		$model	= new Model_Link( $this->env );
		$link	= $model->get( $linkId );
		if( !$link ){
			$this->env->getMessenger()->noteError( 'Invalid link ID' );
			$this->restart( NULL, TRUE );
		}
		header( 'Location: '.$link->url );
		exit;
	}

	public function remove( $noteId ){
		$logic		= new Logic_Note( $this->env );
		$logic->removeNote( $noteId );
		$words		= (object) $this->getWords( 'msg' );
		$this->env->getMessenger()->noteSuccess( $words->successNoteRemoved );
		$this->restart( './work/note' );
	}

	public function removeTag( $noteId, $tagId ){
		$logic		= new Logic_Note( $this->env );
		$words		= (object) $this->getWords( 'msg' );
		$logic->removeTagFromNote( $tagId, $noteId );
		$this->env->getMessenger()->noteSuccess( $words->successNoteTagRemoved );
		$this->restart( './work/note/edit/'.$noteId );
	}

	public function removeLink( $noteId, $noteLinkId ){
		$logic		= new Logic_Note( $this->env );
		$logic->removeNoteLink( $noteLinkId );
		$words		= (object) $this->getWords( 'msg' );
		$this->env->getMessenger()->noteSuccess( $words->successNoteLinkRemoved );
		$this->restart( './work/note/edit/'.$noteId );
	}

	public function view( $noteId ){
		$request	= $this->env->getRequest();
		$logic		= new Logic_Note( $this->env );
		$logic->countNoteView( $noteId );
		$note		= $logic->getNoteData( $noteId );
		if( !$note ){
			$this->env->getMessenger()->noteError( 'Invalid Note ID');
			$this->restart( './work/note/' );
		}
		$this->addData( 'note', $note );
	}
}
?>
