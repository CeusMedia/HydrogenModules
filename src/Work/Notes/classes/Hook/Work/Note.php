<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Note extends Hook
{
	/**
	 * @return void
	 * @throws ReflectionException
	 */
	public function onProjectRemove(): void
	{
		$projectId	= $this->payload['projectId'];
		$model		= new Model_Note( $this->env );
		$logic		= Logic_Note::getInstance( $this->env );
		foreach( $model->getAllByIndex( 'projectId', $projectId ) as $note ){
			$logic->removeNote( $note->noteId );
		}
	}

	/**
	 * @return void
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function onListProjectRelations(): void
	{
		$modelProject	= new Model_Project( $this->env );
		if( empty( $this->payload['projectId'] ) ){
			$message	= 'Hook "Work_Notes::onListProjectRelations" is missing project ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		if( !( $project = $modelProject->get( $this->payload['projectId'] ) ) ){
			$message	= 'Hook "Work_Notes::onListProjectRelations": Invalid project ID.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		$this->payload['activeOnly']	= $this->payload['activeOnly'] ?? FALSE;
		$this->payload['linkable']		= $this->payload['linkable'] ?? FALSE;
		$language		= $this->env->getLanguage();
//		$statusesActive	= [0, 1, 2, 3, 4, 5];
		$list			= [];
		$modelNote		= new Model_Note( $this->env );
		$indices		= ['projectId' => $this->payload['projectId']];
//		if( $this->payload['activeOnly'] )
//			$indices['status']	= $statusesActive;
		$orders			= ['status' => 'ASC', 'title' => 'ASC'];
		$notes			= $modelNote->getAllByIndices( $indices, $orders );	//  ...
/*		$icons			= array(
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-exclamation'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-wrench'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-lightbulb-o'] ),
		);*/
		$icon		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-sticky-note-o', 'title' => 'Notiz'] );
		$words		= $language->getWords( 'work/note' );
		foreach( $notes as $note ){
			$isOpen		= TRUE;//in_array( $issue->status, $statusesActive );
//			$status		= '('.$words['states'][$issue->status].')';
//			$status		= HtmlTag::create( 'small', $status, ['class' => 'muted'] );
			$title		= $isOpen ? $note->title : HtmlTag::create( 'del', $note->title );
			$label		= $icon.'&nbsp;'.$title;//.'&nbsp;'.$status;
			$list[]		= (object) [
				'id'		=> $this->payload['linkable'] ? $note->noteId : NULL,
				'label'		=> $label,
			];
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$this->payload,																					//  hook content data
			$this->module,																				//  module called by hook
			'entity',																				//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Work_Note',																			//  controller of entity
			'view'																					//  action to view or edit entity
		);
	}

	/**
	 * @return void
	 * @throws ReflectionException
	 */
	public function onUserRemove(): void
	{
		$userId		= $this->payload['userId'];
		$model		= new Model_Note( $this->env );
		$logic		= Logic_Note::getInstance( $this->env );
		$notes		= $model->getAllByIndex( 'userId', $userId );
		foreach( $notes as $note )
			$logic->removeNote( $note->noteId );
		if( isset( $this->payload['counts'] ) )
			$this->payload['counts']['Work_Notes']	= (object) ['entities' => count( $notes )];
	}

	/**
	 * @return void
	 */
	public function onListUserRelations(): void
	{
		$userId		= $this->payload['userId'];
		$model		= new Model_Note( $this->env );
		$notes		= $model->getAllByIndex( 'userId', $userId );
		$language	= $this->env->getLanguage();
		$words		= $language->getWords( 'work/note' );
		$icon		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-sticky-note-o', 'title' => 'Notiz'] );
		$list		= [];
		foreach( $notes as $note ){
			$isOpen		= TRUE;//in_array( $issue->status, $statusesActive );
//			$status		= '('.$words['states'][$issue->status].')';
//			$status		= HtmlTag::create( 'small', $status, ['class' => 'muted'] );
			$title		= $isOpen ? $note->title : HtmlTag::create( 'del', $note->title );
			$label		= $icon.'&nbsp;'.$title;//.'&nbsp;'.$status;
			$list[]		= (object) [
				'id'		=> $this->payload['linkable'] ? $note->noteId : NULL,
				'label'		=> $label,
			];
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$this->payload,																					//  hook content data
			$this->module,																			//  module called by hook
			'entity',																				//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Work_Note',																			//  controller of entity
			'view'																					//  action to view or edit entity
		);
	}
}
