<?php

use CeusMedia\Common\Alg\Time\Clock;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Note extends Logic
{
	protected $modelNote;
	protected $modelNoteLink;
	protected $modelNoteTag;
	protected $modelLink;
	protected $modelTag;
	protected $prefix;

	protected $projectId	= 0;
	protected $userId		= 0;
	protected $roleId		= 0;
	protected $visibility	= 0;

	protected $userNoteIds	= [];
	protected $userProjects	= [];

	public function addLinkToNote( $linkId, $noteId, $title = NULL, $strict = TRUE )
	{
		$conditions	= ['noteId' => $noteId, 'linkId' => $linkId, 'title' => $title];
		$relation	= $this->modelNoteLink->getAll( $conditions );
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
		return $this->modelNoteLink->add( $data );
	}

	public function addTagToNote( $tagId, $noteId, $status = Model_Note_Tag::STATUS_NORMAL, $strict = TRUE )
	{
		$indices	= [
			'noteId'	=> $noteId,
			'tagId'		=> $tagId,
			'status'	=> $status,
		];
		if( ( $relation	= $this->modelNoteTag->getByIndices( $indices ) ) ){
			if( $strict )
				throw new InvalidArgumentException( 'tag already related to note' );
			return $relation->noteTagId;
		}
		$indices	= [
			'noteId'	=> $noteId,
			'tagId'		=> $tagId,
		];
		if( ( $relation = $this->modelNoteTag->getByIndices( $indices ) ) ){
			if( $relation->status != $status ){
				$this->modelNoteTag->edit( $relation->noteTagId, array(
					'status'		=> $status,
					'modifiedAt'	=> time(),
				) );
			}
			return $relation->noteTagId;
		}
		$data	= array(
			'noteId'		=> (int) $noteId,
			'tagId'			=> (int) $tagId,
			'status'		=> $status,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		);
		return $this->modelNoteTag->add( $data );
	}

	public function ignoreTagOnNote( $tagId, $noteId, $strict = TRUE )
	{
		return $this->addTagToNote( $tagId, $noteId, Model_Note_Tag::STATUS_DISABLED, TRUE );
	}

	public function countNoteView( $noteId )
	{
		$query	= 'UPDATE '.$this->prefix.'notes SET numberViews=numberViews+1 WHERE noteId='.(int)$noteId;
		$this->env->getDatabase()->query( $query );
	}

	public function createLink( $url, $strict = TRUE )
	{
		$existingLink		= $this->modelLink->getByIndex( 'url', $url );
		if( $existingLink ){
			if( $strict )
				throw new InvalidArgumentException( 'link already existing' );
			return $existingLink->linkId;
		}
		return $this->modelLink->add( array( 'url' => $url, 'createdAt' => time() ) );
	}

	public function createTag( $content, $strict = TRUE )
	{
		$existingTag		= $this->modelTag->getByIndex( 'content', $content );
		if( $existingTag ){
			if( $strict )
				throw new InvalidArgumentException( 'tag already existing' );
			return $existingTag->tagId;
		}
		return $this->modelTag->add( array( 'content' => $content, 'createdAt' => time() ) );
	}

	public function getNoteData( $noteId )
	{
		$note		= $this->modelNote->get( $noteId );
		if( !$note ){
			$this->env->getMessenger()->noteError( 'Invalid note ID' );
			$this->restart( './work/note/index' );
		}

		$note->links	= [];
		foreach( $this->modelNoteLink->getAllByIndex( 'noteId', $noteId ) as $relation ){
			$link		= clone $this->modelLink->get( $relation->linkId );
			$link->noteLinkId	= $relation->noteLinkId;
			$link->title		= $relation->title;
			$note->links[]		= $link;
		}

		$note->tags	= [];
		$indices	= [
			'noteId'	=> $noteId,
			'status'	=> '>= '.Model_Note_Tag::STATUS_NORMAL,
		];
		foreach( $this->modelNoteTag->getAllByIndices( $indices ) as $tag )
			$note->tags[]	= $this->modelTag->get( $tag->tagId );
		return $note;
	}

	/**
	 *	Returns list of note IDs related to tag IDs.
	 *	@access		public
	 *	@param		array		$tagIds		List of tag IDs
	 *	@param		boolean		$strict		Notes must be related to ALL tag IDs (slower)
	 *	@return		array					List of note IDs related to tag IDs
	 */
	public function getNoteIdsFromTagIds( $tagIds, $strict = FALSE )
	{
		if( !is_array( $tagIds ) )
			throw new InvalidArgumentException( 'Tag list must be an array' );
		if( !count( $tagIds ) )
			throw new InvalidArgumentException( 'Tag list cannot be empty' );

		if( $strict ){
			$noteIds	= [];
			$tagIds		= array_unique( $tagIds );
			foreach( $tagIds as $tagId ){
				$indices	= [
					'tagId'		=> $tagId,
					'status'	=> '>= '.Model_Note_Tag::STATUS_NORMAL,
				];
				foreach( $this->modelNoteTag->getAllByIndices( $indices ) as $relation ){
					if( !isset( $noteIds[$relation->noteId] ) )
						$noteIds[$relation->noteId]	= [];
					$noteIds[$relation->noteId][]	= $relation->tagId;
				}
			}
			foreach( $noteIds as $noteId => $tagsFound )
				if( count( $tagsFound ) !== count( $tagIds ) )
					unset( $noteIds[$noteId] );
			return array_keys( $noteIds );
		}

		$noteIds			= [];
		$indices	= [
			'tagId'		=> $tagIds,
			'status'	=> '>= '.Model_Note_Tag::STATUS_NORMAL,
		];
		foreach( $this->modelNoteTag->getAllByIndices( $indices ) as $relation )
			$noteIds[]	= $relation->noteId;
		return $noteIds;
	}

	public function getRelatedTags( $noteId )
	{
		$relatedNoteIds	= $this->getRelatedNoteIds( $noteId );

		$noteTags	= [];
		$indices	= [
			'noteId'	=> $noteId,
			'status'	=> '>= '.Model_Note_Tag::STATUS_NORMAL,
		];
		foreach( $this->modelNoteTag->getAllByIndices( $indices ) as $noteTag )
			$noteTags[]	= $noteTag->tagId;

		$list		= [];
		foreach( $relatedNoteIds as $relatedNoteId => $count ){
			$indices	= [
				'noteId'	=> $relatedNoteId,
				'status'	=> '>= '.Model_Note_Tag::STATUS_NORMAL,
			];
			$relatedNoteTags	= $this->modelNoteTag->getAllByIndices( $indices );
			foreach( $relatedNoteTags as $relatedNoteTag ){
				if( !isset( $list[$relatedNoteTag->tagId] ) )
					$list[$relatedNoteTag->tagId]	= 0;
				$list[$relatedNoteTag->tagId]		+= $count;
			}
		}
		$indices	= [
			'noteId'	=> $noteId,
			'status'	=> '< '.Model_Note_Tag::STATUS_NORMAL,
		];
		foreach( $this->modelNoteTag->getAllByIndices( $indices ) as $noteTag )
			if( isset( $list[$noteTag->tagId] ) )
				unset( $list[$noteTag->tagId] );
		arsort( $list );

		$relatedTagIds		= [];
		foreach( $list as $tagId => $count ){
			if( in_array( $tagId, $noteTags ) )
				continue;
			$relatedTag	= $this->modelTag->get ( $tagId );
			$relatedTag->relevance	= $count;
			$relatedTagIds[]	= $relatedTag;
		}
		return $relatedTagIds;
	}

	public function getRelatedNoteIds( $noteId )
	{
		$relatedNoteIds	= [];
		$indices	= [
			'noteId'	=> $noteId,
			'status'	=> '>= '.Model_Note_Tag::STATUS_NORMAL,
		];
		$noteTags		= $this->modelNoteTag->getAllByIndices( $indices );
		foreach( $noteTags as $noteTag ){
			$indices	= [
				'tagId'		=> $noteTag->tagId,
				'status'	=> '>= '.Model_Note_Tag::STATUS_NORMAL,
			];
			$notes		= $this->modelNoteTag->getAllByIndices( $indices );
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

/*	public function getNoteIdsFromTagIds( $tagIds = [] ){
		if( !is_array( $tagIds ) )
			throw new InvalidArgumentException( 'Tag list must be an array' );
		if( !count( $tagIds ) )
			throw new InvalidArgumentException( 'Tag list cannot be empty' );

		$noteIds			= [];
		foreach( $this->modelNoteTag->getAllByIndices( ['tagId' => $tagIds, 'status' => '>= '.Model_Note_Tag::STATUS_NORMAL] ) as $relation )
			$noteIds[]	= $relation->noteId;
		return $noteIds;
	}
*/
	public function getRankedTagIdsFromNoteIds( $noteIds, $skipTagIds = [] )
	{
		$tagIds	= [];
		$indices	= [
			'noteId'	=> $noteIds,
			'status'	=> '>= '.Model_Note_Tag::STATUS_NORMAL,
		];
		foreach( $this->modelNoteTag->getAllByIndices( $indices ) as $relation ){
			if( !in_array( $relation->tagId, $skipTagIds ) ){
				if( !isset( $tagIds[$relation->tagId] ) )
					$tagIds[$relation->tagId]	= 0;
				$tagIds[$relation->tagId]++;
			}
		}
		arsort( $tagIds );
		return $tagIds;
	}

	/**
	 *	@todo finish implementation or remove
	 */
	public function getRelatedTagsFromTags( $tagIds = [] )
	{
		if( !is_array( $tagIds ) )
			throw new InvalidArgumentException( 'Tag list must be an array' );
		if( !count( $tagIds ) )
			throw new InvalidArgumentException( 'Tag list cannot be empty' );
	}

	public function getTopNotes( $conditions = [], $orders = [], $limits = [] )
	{
		$clock		= new Clock();
		if( !$orders )
			$orders		= [
				'modifiedAt'	=> 'DESC',
				'createdAt'		=> 'DESC',
				'title'			=> 'ASC',
			];
		if( !$limits )
			$limits		= [0, 10];
		$conditions	= $this->sharpenConditions( $conditions );
		$number		= $this->modelNote->count( $conditions );
		if( $number < $limits[0] )
			$limits[0]	= 0;
		$notes	= $this->modelNote->getAll( $conditions, $orders, $limits );
		foreach( $notes as $nr => $note )
			$notes[$nr]	= $this->populateNote( $note );
		return array(
			'number'	=> $number,
			'time'		=> $clock->stop( 6, 0 ),
			'list'		=> $notes
		);
	}

	public function getTopTags( $limit = 10, $offset = 0, $projectId = NULL, $notTagIds = [] )
	{
		if( $notTagIds ){
			$noteIds	= $this->getNoteIdsFromTagIds( $notTagIds, !TRUE );
			if( $this->userId && $this->userProjects )
				$noteIds	= array_intersect( $noteIds, $this->userNoteIds );
			if( $noteIds ){
				$tagIds		= $this->getRankedTagIdsFromNoteIds( $noteIds, $notTagIds );
				$tags		= [];
				if( $tagIds ){
					$tags		= $this->modelTag->getAllByIndices( ['tagId' => array_keys( $tagIds )] );
					$tags		= array_slice( $tags, $offset, $limit, TRUE );
					foreach( $tags as $nr => $tag )
						$tags[$nr]->relations	= $tagIds[$tag->tagId];
				}
			}
			return $tags;
		}

		$tagIds	= [];
		$conditions	= [
			'status'	=> '>= '.Model_Note_Tag::STATUS_NORMAL
		];
		if( $this->userId && $this->userProjects )
			$conditions['noteId']	= array_merge( [0], $this->userNoteIds );
		foreach( $this->modelNoteTag->getAll( $conditions ) as $relation ){
			if( !isset( $tagIds[$relation->tagId] ) )
				$tagIds[$relation->tagId]	= 0;
			$tagIds[$relation->tagId]++;
		}
		arsort( $tagIds );
		$tagIds		= array_slice( $tagIds, $offset, $limit, TRUE );
		if( $tagIds ){
			$tags		= $this->modelTag->getAllByIndices( ['tagId' => array_keys( $tagIds )] );
			foreach( $tags as $nr => $tag ){
				$tag->relations	= $tagIds[$tag->tagId];
				$tagIds[$tag->tagId]	= $tag;
			}
		}
		return array_values( $tagIds );
	}

	public function populateNote( $note )
	{
		$note->links	= [];
		$note->tags		= [];

		$links	= $this->modelNoteLink->getAllByIndex( 'noteId', $note->noteId );
		$tags	= $this->modelNoteTag->getAllByIndices( [
			'noteId'	=> $note->noteId,
			'status'	=> '>= '.Model_Note_Tag::STATUS_NORMAL,
		] );

		foreach( $links as $relation ){
			$link			= $this->modelLink->get( $relation->linkId );
			$link->title	= $relation->title;
			$note->links[]	= $link;
		}

		foreach( $tags as $tag )
			$note->tags[]	= $this->modelTag->get( $tag->tagId );

		return $note;
	}

	public function removeNote( $noteId )
	{
		$relatedTags	= $this->modelNoteTag->getAllByIndex( 'noteId', $noteId );					//  get tag relations
		foreach( $relatedTags as $relatedTag ){														//  iterate tag relations
			$this->modelNoteTag->remove( $relatedTag->noteTagId );									//  remove relation to tag
			if( !$this->modelNoteTag->countByIndex( 'tagId', $relatedTag->tagId ) )					//  tag is not related by other notes
				$this->modelTag->remove( $relatedTag->tagId );										//  remove tag itself
		}
		$relatedLinks	= $this->modelNoteLink->getAllByIndex( 'noteId', $noteId );					//  get link relations
		foreach( $relatedLinks as $relatedLink ){													//  iterate link relations
			$this->modelNoteLink->remove( $relatedLink->noteLinkId );								//  remove relation to link
			if( !$this->modelNoteLink->countByIndex( 'linkId', $relatedLink->linkId ) )				//  link is not related by other notes
				$this->modelLink->remove( $relatedLink->linkId );									//  remote link itself
		}
		$this->modelNote->remove( $noteId );														//  remote note
	}

	public function removeNoteLink( $noteLinkId )
	{
		if( !$this->modelNoteLink->get( $noteLinkId ) )
			return FALSE;
		$this->modelNoteLink->remove( $noteLinkId );
		return TRUE;
	}

	public function removeLinkFromNote( $linkId, $noteId )
	{
		$indices		= ['noteId' => $noteId, 'linkId' => $linkId];						//  focus on note and link
		$this->modelNoteLink->removeByIndices( $indices );											//  remove note link relation
		$relations	= $this->modelNoteLink->getAllByIndex( 'linkId', $linkId );						//  find other link relations
		if( !count( $relations ) )																	//  link is unrelated now
			$this->modelLink->remove( $linkId );													//  remove link
	}

	public function removeTagFromNote( $tagId, $noteId )
	{
		$indices		= ['noteId' => $noteId, 'tagId' => $tagId];							//  focus on note and tag
		$this->modelNoteTag->removeByIndices( $indices );											//  remove note tag relation
		$relations	= $this->modelNoteTag->getAllByIndex( 'tagId', $tagId );						//  find other tag relations
		if( !count( $relations ) )																	//  tag is unrelated now
			$this->modelTag->remove( $tagId );														//  remove tag
	}

	/**
	 *	@todo		use of GREATEST only works for MySQL - improve this!
	 *	@see		http://stackoverflow.com/questions/71022/sql-max-of-multiple-columns
	 */
	public function searchNotes( $query, $conditions, $orders = [], $limits = [] )
	{
//		if( !strlen( trim( $query ) ) && !$tags )
//			throw new Exception( 'Neither query nor tags to search for given' );
		if( !strlen( trim( $query ) ) )
			throw new Exception( 'No search termed given' );

		if( !$limits )
			$limits	= [0, 10];

		$cond		= [];
		$pattern	= '/^(<=|>=|<|>|!=)(.+)/';
		foreach( $conditions as $column => $value ){
			if( is_array( $value ) )
				$cond[]	= "n.".$column.' IN ('.join( ',', $value ).')';
			else if( preg_match( '/^%/', $value ) )
				$cond[]	= "n.".$column." LIKE '".$value."'";
			else if( preg_match( $pattern, $value ) ){
				$matches	= [];
				preg_match_all( $pattern, $value, $matches );
				$operation	= ' '.$matches[1][0].' ';
				$cond[]	= "n.".$column.$operation."'".$matches[2][0]."'";
			}
			else
				$cond[]	= "n.".$column." = '".$value."'";
		}
		$conditions	= $cond;

		if( $query ){
			$terms 	= explode( ' ', trim( $query ) );
			foreach( $terms as $term ){
				$ors	= [
					'n.title LIKE "%'.$term.'%"',
					'n.content LIKE "%'.$term.'%"',
					'l.url LIKE "%'.$term.'%"',
					'nl.title LIKE "%'.$term.'%"',
					't.content="'.$term.'"'
				];
				$conditions[]	= '('.join( ' OR ', $ors ).')';
			}
		}
		$conditions	= implode ( ' AND ', $conditions );
		$query	= '
SELECT
	DISTINCT(n.noteId),
	n.*,
	GREATEST(n.createdAt, n.modifiedAt) AS touchedAt
FROM
	'.$this->prefix.'notes as n LEFT OUTER JOIN
	'.$this->prefix.'note_links as nl ON(n.noteId=nl.noteId) LEFT OUTER JOIN
	'.$this->prefix.'note_tags as nt ON(n.noteId=nt.noteId) LEFT OUTER JOIN
	'.$this->prefix.'links as l ON(nl.linkId=l.linkId) LEFT OUTER JOIN
	'.$this->prefix.'tags as t ON(nt.tagId=t.tagId)
WHERE
	'.$conditions.'
ORDER BY
	touchedAt DESC
';
//xmp( $query );die;
		$clock		= new Clock();
		$result		= $this->env->getDatabase()->query( $query );
		$notes	= $result->fetchAll( PDO::FETCH_OBJ );
		$number		= count( $notes );
		$notes	= array_slice( $notes, $limits[0], $limits[1] );
		foreach( $notes as $nr => $note )
			$notes[$nr]	= $this->populateNote( $note );
		return array(
			'number'	=> $number,
			'time'		=> $clock->stop( 6, 0 ),
			'list'		=> $notes
		);
	}

	public function setContext( $userId, $roleId, $projectId )
	{
		$this->userId			= $userId;
		$this->roleId			= $roleId;
		$this->projectId		= $projectId;
		$this->userNoteIds		= [];
		$this->userProjects		= [];
		if( $this->userId ){
			if( $this->env->getModules()->has( 'Manage_Projects' ) ){
				$logicProject	= Logic_Project::getInstance( $this->env );
				$userProjects	= $logicProject->getUserProjects( $this->userId );
				foreach( $userProjects as $userProject )
					$this->userProjects[$userProject->projectId]	= $userProject;
				$projectIds	= array_merge( [0], array_keys( $this->userProjects ) );
				if( strlen( trim( $this->projectId ) ) )
					$projectIds	= array_merge( [$this->projectId] );
				$userNotes	= $this->modelNote->getAll( ['projectId' => $projectIds] );
				foreach( $userNotes as $userNote )
					$this->userNoteIds[]	= $userNote->noteId;
			}
		}
	}

	protected function __onInit(): void
	{
		$this->modelNote		= new Model_Note( $this->env );
		$this->modelNoteLink	= new Model_Note_Link( $this->env );
		$this->modelNoteTag		= new Model_Note_Tag( $this->env );
		$this->modelLink		= new Model_Link( $this->env );
		$this->modelTag			= new Model_Tag( $this->env );
		$this->prefix			= $this->env->getDatabase()->getPrefix();
	}

	protected function sharpenConditions( $conditions )
	{
		if( $this->env->has( 'acl' ) )
			if( $this->env->get( 'acl' )->hasFullAccess( $this->roleId ) )
				return $conditions;

//		if( !array_key_exists( 'userId', $conditions ) || $conditions['userId'] != $this->userId )
//			if( !array_key_exists( 'public', $conditions ) || $conditions['public'] != 1 )
//				$conditions['public']	= 1;

/*		$logic			= Logic_Project::getInstance( $this->env );
		$userProjects	= [0];
		foreach( $logic->getUserProjects( $this->userId ) as $relation )
			$userProjects[]	= $relation->projectId;
		if( !array_key_exists( 'projectId', $conditions ) || !in_array( $conditions['projectId'], $userProjects ) )
			$conditions['projectId']	= $userProjects;
*/		return $conditions;
	}
}
