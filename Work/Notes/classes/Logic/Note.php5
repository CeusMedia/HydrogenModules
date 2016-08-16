<?php
class Logic_Note{

	static protected $instance;

	protected $projectId	= 0;
	protected $userId		= 0;
	protected $roleId		= 0;
	protected $visibility	= 0;

	protected $userNoteIds	= array();
	protected $userProjects	= array();

	protected function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env			= $env;
		$this->modelNote	= new Model_Note( $env );
		$this->prefix		= $env->getConfig()->get( 'database.prefix' );
	}

	protected function __clone(){}

	static public function getInstance( CMF_Hydrogen_Environment_Abstract $env ){
		if( !self::$instance )
			self::$instance	= new Logic_Note( $env );
		return self::$instance;
	}

	public function setContext( $userId, $roleId, $projectId ){
		$this->userId			= $userId;
		$this->roleId			= $roleId;
		$this->projectId		= $projectId;
		$this->userNoteIds		= array();
		$this->userProjects		= array();
		if( $this->userId ){
			if( $this->env->getModules()->has( 'Manage_Projects' ) ){
				$logicProject	= new Logic_Project( $this->env );
				$userProjects	= $logicProject->getUserProjects( $this->userId );
				foreach( $userProjects as $userProject )
					$this->userProjects[$userProject->projectId]	= $userProject;
				$projectIds	= array_merge( array( 0 ), array_keys( $this->userProjects ) );
				if( strlen( trim( $this->projectId ) ) )
					$projectIds	= array_merge( array( $this->projectId ) );
				$userNotes	= $this->modelNote->getAll( array( 'projectId' => $projectIds ) );
				foreach( $userNotes as $userNote )
					$this->userNoteIds[]	= $userNote->noteId;
			}
		}
	}

	protected function sharpenConditions( $conditions ){
		if( $this->env->has( 'acl' ) )
			if( $this->env->get( 'acl' )->hasFullAccess( $this->roleId ) )
				return $conditions;

//		if( !array_key_exists( 'userId', $conditions ) || $conditions['userId'] != $this->userId )
//			if( !array_key_exists( 'public', $conditions ) || $conditions['public'] != 1 )
//				$conditions['public']	= 1;

/*		$logic			= new Logic_Project( $this->env );
		$userProjects	= array( 0 );
		foreach( $logic->getUserProjects( $this->userId ) as $relation )
			$userProjects[]	= $relation->projectId;
		if( !array_key_exists( 'projectId', $conditions ) || !in_array( $conditions['projectId'], $userProjects ) )
			$conditions['projectId']	= $userProjects;
*/		return $conditions;
	}

	public function countNoteView( $noteId ){
		$query	= 'UPDATE '.$this->prefix.'notes SET numberViews=numberViews+1 WHERE noteId='.(int)$noteId;
		$this->env->getDatabase()->query( $query );
	}

	public function addLinkToNote( $linkId, $noteId, $title = NULL, $strict= TRUE ){
		$model		= new Model_Note_Link( $this->env );
		$conditions	= array( 'noteId' => $noteId, 'linkId' => $linkId, 'title' => $title );
		$relation	= $model->getAll( $conditions );
		if( $relation ){
			if( $strict )
				throw new InvalidArgumentException( 'link already related to note' );
			return $relation[0]->noteLinkId;
		}
		$data	= array(
			'noteId'	=> (int) $noteId,
			'linkId'	=> (int) $linkId,
			'title'		=> $title,
			'createdAt'	=> time(),
		);
		return $model->add( $data );
	}

	public function addTagToNote( $tagId, $noteId, $strict= TRUE ){
		$model		= new Model_Note_Tag( $this->env );
		$indices	= array( 'noteId' => $noteId, 'tagId' => $tagId );
		$relation	= $model->getByIndices( $indices );
		if( $relation ){
			if( $strict )
				throw new InvalidArgumentException( 'tag already related to note' );
			return $relation->noteTagId;
		}
		$data	= array(
			'noteId'	=> (int) $noteId,
			'tagId'		=> (int) $tagId,
			'createdAt'	=> time(),
		);
		return $model->add( $data );
	}

	public function createLink( $url, $strict = TRUE ){
		$modelLink			= new Model_Link( $this->env );
		$existingLink		= $modelLink->getByIndex( 'url', $url );
		if( $existingLink ){
			if( $strict )
				throw new InvalidArgumentException( 'link already existing' );
			return $existingLink->linkId;
		}
		return $modelLink->add( array( 'url' => $url, 'createdAt' => time() ) );
	}

	public function createTag( $content, $strict = TRUE ){
		$modelTag			= new Model_Tag( $this->env );
		$existingTag		= $modelTag->getByIndex( 'content', $content );
		if( $existingTag ){
			if( $strict )
				throw new InvalidArgumentException( 'tag already existing' );
			return $existingTag->tagId;
		}
		return $modelTag->add( array( 'content' => $content, 'createdAt' => time() ) );
	}

	public function getNoteData( $noteId ){
		$modelNote		= new Model_Note( $this->env );
		$modelNoteLink	= new Model_Note_Link( $this->env );
		$modelNoteTag	= new Model_Note_Tag( $this->env );
		$modelLink		= new Model_Link( $this->env );
		$modelTag		= new Model_Tag( $this->env );
		$note		= $modelNote->get( $noteId );
		if( !$note ){
			$this->env->getMessenger()->noteError( 'Invalid note ID' );
			$this->restart( './work/note/index' );
		}

		$note->links	= array();
		foreach( $modelNoteLink->getAllByIndex( 'noteId', $noteId ) as $relation ){
			$link		= clone $modelLink->get( $relation->linkId );
			$link->noteLinkId	= $relation->noteLinkId;
			$link->title		= $relation->title;
			$note->links[]		= $link;
		}

		$note->tags	= array();
		foreach( $modelNoteTag->getAllByIndex( 'noteId', $noteId ) as $tag )
			$note->tags[]	= $modelTag->get( $tag->tagId );
		return $note;
	}

	public function getRelatedTags( $noteId ){
		$modelNote		= new Model_Note( $this->env );
		$modelNoteTag	= new Model_Note_Tag( $this->env );
		$modelTag			= new Model_Tag( $this->env );
		$relatedNoteIds	= $this->getRelatedNoteIds( $noteId );

		$noteTags		= array();
		foreach( $modelNoteTag->getAllByIndex( 'noteId', $noteId ) as $noteTag )
			$noteTags[]	= $noteTag->tagId;

		$list		= array();
		foreach( $relatedNoteIds as $relatedNoteId => $count ){
			$relatedNoteTags	= $modelNoteTag->getAllByIndex( 'noteId', $relatedNoteId );
			foreach( $relatedNoteTags as $relatedNoteTag ){
				if( !isset( $list[$relatedNoteTag->tagId] ) )
					$list[$relatedNoteTag->tagId]	= 0;
				$list[$relatedNoteTag->tagId]		+= $count;
			}
		}
		arsort( $list );

		$relatedTagIds		= array();
		foreach( $list as $tagId => $count ){
			if( in_array( $tagId, $noteTags ) )
				continue;
			$relatedTag	= $modelTag->get ( $tagId );
			$relatedTag->relevance	= $count;
			$relatedTagIds[]	= $relatedTag;
		}
		return $relatedTagIds;
	}

	public function getRelatedNoteIds( $noteId ){
		$relatedNoteIds	= array();
		$modelNoteTag	= new Model_Note_Tag( $this->env );
		$noteTags		= $modelNoteTag->getAllByIndex( 'noteId', $noteId );
		foreach( $noteTags as $noteTag ){
			$notes		= $modelNoteTag->getAllByIndex( 'tagId', $noteTag->tagId );
			foreach( $notes as $note ){
				if( $note->noteId != $noteId ){
					if( !isset( $list[$note->noteId] ) )
						$relatedNoteIds[$note->noteId]	= 0;
					$relatedNoteIds[$note->noteId]++ ;
				}
			}
		}
		arsort( $relatedNoteIds );
		if( $this->userId && $this->userProjects )
			$relatedNoteIds	= array_intersect( $relatedNoteIds, $this->userNoteIds );
		return $relatedNoteIds;
	}

/*	public function getNoteIdsFromTagIds( $tagIds = array() ){
		if( !is_array( $tagIds ) )
			throw new InvalidArgumentException( 'Tag list must be an array' );
		if( !count( $tagIds ) )
			throw new InvalidArgumentException( 'Tag list cannot be empty' );

		$modelNoteTag	= new Model_Note_Tag( $this->env );
		$noteIds			= array();
		foreach( $modelNoteTag->getAllByIndices( array( 'tagId' => $tagIds ) ) as $relation )
			$noteIds[]	= $relation->noteId;
		return $noteIds;
	}
*/
	public function getRankedTagIdsFromNoteIds( $noteIds, $skipTagIds = array() ){
		$modelNoteTag	= new Model_Note_Tag( $this->env );
		$tagIds	= array();
		foreach( $modelNoteTag->getAllByIndices( array( 'noteId' => $noteIds ) ) as $relation ){
			if( !in_array( $relation->tagId, $skipTagIds ) ){
				if( !isset( $tagIds[$relation->tagId] ) )
					$tagIds[$relation->tagId]	= 0;
				$tagIds[$relation->tagId]++;
			}
		}
		arsort( $tagIds );
		return $tagIds;
	}

	public function getRelatedTagsFromTags( $tagIds = array() ){
		if( !is_array( $tagIds ) )
			throw new InvalidArgumentException( 'Tag list must be an array' );
		if( !count( $tagIds ) )
			throw new InvalidArgumentException( 'Tag list cannot be empty' );
	}

	public function getTopTags( $limit = 10, $offset = 0, $projectId = NULL, $notTagIds = array() ){
		$modelNotes		= new Model_Note( $this->env );
		$modelTag		= new Model_Tag( $this->env );

		if( $notTagIds ){
			$noteIds	= $this->getNoteIdsFromTagIds( $notTagIds, !TRUE );
			if( $this->userId && $this->userProjects )
				$noteIds	= array_intersect( $noteIds, $this->userNoteIds );
			if( $noteIds ){
				$tagIds		= $this->getRankedTagIdsFromNoteIds( $noteIds, $notTagIds );
				$tags		= array();
				if( $tagIds ){
					$tags		= $modelTag->getAllByIndices( array( 'tagId' => array_keys( $tagIds ) ) );
					$tags		= array_slice( $tags, $offset, $limit, TRUE );
					foreach( $tags as $nr => $tag )
						$tags[$nr]->relations	= $tagIds[$tag->tagId];
				}
			}
			return $tags;
		}

		$tagIds	= array();
		$modelRel	= new Model_Note_Tag( $this->env );
		$conditions	= array();
		if( $this->userId && $this->userProjects )
			$conditions['noteId']	= array_merge( array( 0 ), $this->userNoteIds );
		foreach( $modelRel->getAll( $conditions ) as $relation ){
			if( !isset( $tagIds[$relation->tagId] ) )
				$tagIds[$relation->tagId]	= 0;
			$tagIds[$relation->tagId]++;
		}
		arsort( $tagIds );
		$tagIds		= array_slice( $tagIds, $offset, $limit, TRUE );
		if( $tagIds ){
			$tags		= $modelTag->getAllByIndices( array( 'tagId' => array_keys( $tagIds ) ) );
			foreach( $tags as $nr => $tag ){
				$tag->relations	= $tagIds[$tag->tagId];
				$tagIds[$tag->tagId]	= $tag;
			}
		}
		return array_values( $tagIds );
	}

	public function removeNote( $noteId ){
		$note	= $this->getNoteData( $noteId );
		foreach( $note->tags as $tag )
			$this->removeTagFromNote( $tag->tagId, $noteId );
		foreach( $note->links as $link )
			$this->removeNoteLink( $link->noteLinkId );
		$this->modelNote->remove( $noteId );
	}

	public function removeNoteLink( $noteLinkId ){
		$modelNoteLink	= new Model_Note_Link( $this->env );
		if( !$modelNoteLink->get( $noteLinkId ) )
			return FALSE;
		$modelNoteLink->remove( $noteLinkId );
		return TRUE;
	}

	public function removeLinkFromNote( $linkId, $noteId ){
		$modelNoteLink	= new Model_Note_Link( $this->env );
		$modelLink			= new Model_Link( $this->env );
		$indices			= array( 'noteId' => $noteId, 'linkId' => $linkId );				//  focus on note and link
		$modelNoteLink->removeByIndices( $indices );												//  remove note link relation
		$relations	= $modelNoteLink->getAllByIndex( 'linkId', $linkId );						//  find other link relations
		if( !count( $relations ) )																	//  link is unrelated now
			$modelLink->remove( $linkId );															//  remove link
	}

	public function removeTagFromNote( $tagId, $noteId ){
		$modelNoteTag	= new Model_Note_Tag( $this->env );
		$modelTag			= new Model_Tag( $this->env );
		$indices			= array( 'noteId' => $noteId, 'tagId' => $tagId );				//  focus on note and tag
		$modelNoteTag->removeByIndices( $indices );												//  remove note tag relation
		$relations	= $modelNoteTag->getAllByIndex( 'tagId', $tagId );							//  find other tag relations
		if( !count( $relations ) )																	//  tag is unrelated now
			$modelTag->remove( $tagId );															//  remove tag
	}

	public function getTopNotes( $conditions = array(), $offset = 0, $limit = 10 ){
		$model		= new Model_Note( $this->env );
		$clock		= new Alg_Time_Clock();
		$orders		= array(
			'modifiedAt'	=> 'DESC',
			'createdAt'		=> 'DESC',
			'title'			=> 'ASC',
		);
		$conditions	= $this->sharpenConditions( $conditions );
		$number		= $model->count( $conditions );
		if( $number < $offset )
			$offset	= 0;
		$notes	= $model->getAll( $conditions, $orders, array( $offset, $limit ) );
		foreach( $notes as $nr => $note )
			$notes[$nr]	= $this->populateNote( $note );
		return array(
			'number'	=> $number,
			'time'		=> $clock->stop( 6, 0 ),
			'list'		=> $notes
		);
	}

	/**
	 *	Returns list of note IDs related to tag IDs.
	 *	@access		public
	 *	@param		array		$tagIds		List of tag IDs
	 *	@param		boolean		$strict		Notes must be related to ALL tag IDs (slower)
	 *	@return		array					List of note IDs related to tag IDs
	 */
	public function getNoteIdsFromTagIds( $tagIds, $strict = FALSE ){
		if( !is_array( $tagIds ) )
			throw new InvalidArgumentException( 'Tag list must be an array' );
		if( !count( $tagIds ) )
			throw new InvalidArgumentException( 'Tag list cannot be empty' );

		if( $strict ){
			$noteIds	= array();
			$model		= new Model_Note_Tag( $this->env );
			$tagIds		= array_unique( $tagIds );
			foreach( $tagIds as $tagId ){
				foreach( $model->getAll( array( 'tagId' => $tagId ) ) as $relation ){
					if( !isset( $noteIds[$relation->noteId] ) )
						$noteIds[$relation->noteId]	= array();
					$noteIds[$relation->noteId][]	= $relation->tagId;
				}
			}
			foreach( $noteIds as $noteId => $tagsFound )
				if( count( $tagsFound ) !== count( $tagIds ) )
					unset( $noteIds[$noteId] );
			return array_keys( $noteIds );
		}

		$modelNoteTag	= new Model_Note_Tag( $this->env );
		$noteIds			= array();
		foreach( $modelNoteTag->getAllByIndices( array( 'tagId' => $tagIds ) ) as $relation )
			$noteIds[]	= $relation->noteId;
		return $noteIds;
	}

	/**
	 *	@todo		use of GREATEST only works for MySQL - improve this!
	 *	@see		http://stackoverflow.com/questions/71022/sql-max-of-multiple-columns
	 */
	public function searchNotes( $query, $conditions, $tags = array(), $offset = 0, $limit = 10 ){
		if( !strlen( trim( $query ) ) && !$tags )
			throw new Exception( 'Neither query nor tags to search for given' );

		$cond		= array();
		$pattern	= '/^(<=|>=|<|>|!=)(.+)/';
		foreach( $conditions as $column => $value ){
			if( is_array( $value ) )
				$cond[]	= "n.".$column.' IN ('.join( ',', $value ).')';
			else if( preg_match( '/^%/', $value ) )
				$cond[]	= "n.".$column." LIKE '".$value."'";
			else if( preg_match( $pattern, $value ) ){
				$matches	= array();
				preg_match_all( $pattern, $value, $matches );
				$operation	= ' '.$matches[1][0].' ';
				$cond[]	= "n.".$column.$operation."'".$matches[2][0]."'";
			}
			else
				$cond[]	= "n.".$column." = '".$value."'";
		}
		$conditions	= $cond;
//		if( $tags )
//			foreach( $tags as $tag )
//				$query	.= ' '.$tag->content;

		if( $query ){
			$terms 	= explode( ' ', trim( $query ) );
			foreach( $terms as $term )
				$conditions[]	= '(n.title LIKE "%'.$term.'%" OR n.content LIKE "%'.$term.'%" OR l.url LIKE "%'.$term.'%" OR nl.title LIKE "%'.$term.'%")';
		}
		$conditions	= implode ( ' AND ', $conditions );
//		if( $tags ){
//			$noteIds		= $this->getNoteIdsFromTagIds( array_keys( $tags ), TRUE );
//			if( $noteIds )
//				$conditions	.= ($query ? ' AND ' : '' ).' n.noteId IN('.join( ',', $noteIds ).')';
//		}
//xmp( $conditions );
		$query	= '
SELECT
	DISTINCT(n.noteId),
	n.*,
	GREATEST(n.createdAt, n.modifiedAt) AS touchedAt
FROM
	'.$this->prefix.'notes as n LEFT OUTER JOIN
	'.$this->prefix.'note_links as nl ON(n.noteId=nl.noteId) LEFT OUTER JOIN
	'.$this->prefix.'links as l ON(nl.linkId=l.linkId)
WHERE
	'.$conditions.'
ORDER BY
	touchedAt DESC
';
		$clock		= new Alg_Time_Clock();
		$result		= $this->env->getDatabase()->query( $query );
		$notes	= $result->fetchAll( PDO::FETCH_OBJ );
		$number		= count( $notes );
		$notes	= array_slice( $notes, $offset, $limit );
		foreach( $notes as $nr => $note )
			$notes[$nr]	= $this->populateNote( $note );
		return array(
			'number'	=> $number,
			'time'		=> $clock->stop( 6, 0 ),
			'list'		=> $notes
		);
	}

	public function populateNote( $note ){
		$modelNoteLink	= new Model_Note_Link( $this->env );
		$modelNoteTag	= new Model_Note_Tag( $this->env );
		$modelLink			= new Model_Link( $this->env );
		$modelTag			= new Model_Tag( $this->env );

		$note->links	= array();
		$note->tags	= array();

		$links	= $modelNoteLink->getAllByIndex( 'noteId', $note->noteId );
		$tags	= $modelNoteTag->getAllByIndex( 'noteId', $note->noteId );

		foreach( $links as $relation ){
			$link			= $modelLink->get( $relation->linkId );
			$link->title	= $relation->title;
			$note->links[]	= $link;
		}

		foreach( $tags as $tag )
			$note->tags[]	= $modelTag->get( $tag->tagId );

		return $note;
	}
}
?>
