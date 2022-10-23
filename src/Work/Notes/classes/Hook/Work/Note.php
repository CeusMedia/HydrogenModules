<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Note extends Hook
{
	public static function onProjectRemove( Environment $env, $context, $module, $payload )
	{
		$data		= (object) $payload;
		$projectId	= $data->projectId;
		$model		= new Model_Note( $env );
		$logic		= Logic_Note::getInstance( $env );
		foreach( $model->getAllByIndex( 'projectId', $projectId ) as $note ){
			$logic->removeNote( $note->noteId );
		}
	}

	public static function onListProjectRelations( Environment $env, $context, $module, $payload )
	{
		$data		= (object) $payload;
		$modelProject	= new Model_Project( $env );
		if( empty( $data->projectId ) ){
			$message	= 'Hook "Work_Notes::onListProjectRelations" is missing project ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		if( !( $project = $modelProject->get( $data->projectId ) ) ){
			$message	= 'Hook "Work_Notes::onListProjectRelations": Invalid project ID.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$data->activeOnly	= isset( $data->activeOnly ) ? $data->activeOnly : FALSE;
		$data->linkable		= isset( $data->linkable ) ? $data->linkable : FALSE;
		$language		= $env->getLanguage();
//		$statusesActive	= [0, 1, 2, 3, 4, 5];
		$list			= [];
		$modelNote		= new Model_Note( $env );
		$indices		= ['projectId' => $data->projectId];
//		if( $data->activeOnly )
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
			$list[]		= (object) array(
				'id'		=> $data->linkable ? $note->noteId : NULL,
				'label'		=> $label,
			);
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$data,																					//  hook content data
			$module,																				//  module called by hook
			'entity',																				//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Work_Note',																			//  controller of entity
			'view'																					//  action to view or edit entity
		);
	}

	public static function onUserRemove( Environment $env, $context, $module, $payload )
	{
		$data		= (object) $payload;
		$userId		= $data->userId;
		$model		= new Model_Note( $env );
		$logic		= Logic_Note::getInstance( $env );
		$notes		= $model->getAllByIndex( 'userId', $userId );
		foreach( $notes as $note )
			$logic->removeNote( $note->noteId );
		if( isset( $data->counts ) )
			$data->counts['Work_Notes']	= (object) ['entities' => count( $notes )];
	}

	public static function onListUserRelations( Environment $env, $context, $module, $payload )
	{
		$data		= (object) $payload;
		$userId		= $data->userId;
		$model		= new Model_Note( $env );
		$logic		= Logic_Note::getInstance( $env );
		$notes		= $model->getAllByIndex( 'userId', $userId );
		$language	= $env->getLanguage();
		$words		= $language->getWords( 'work/note' );
		$icon		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-sticky-note-o', 'title' => 'Notiz'] );
		$list		= [];
		foreach( $notes as $note ){
			$isOpen		= TRUE;//in_array( $issue->status, $statusesActive );
//			$status		= '('.$words['states'][$issue->status].')';
//			$status		= HtmlTag::create( 'small', $status, ['class' => 'muted'] );
			$title		= $isOpen ? $note->title : HtmlTag::create( 'del', $note->title );
			$label		= $icon.'&nbsp;'.$title;//.'&nbsp;'.$status;
			$list[]		= (object) array(
				'id'		=> $data->linkable ? $note->noteId : NULL,
				'label'		=> $label,
			);
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$data,																					//  hook content data
			$module,																				//  module called by hook
			'entity',																				//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Work_Note',																			//  controller of entity
			'view'																					//  action to view or edit entity
		);
	}

	protected function __onInit()
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= Logic_Note::getInstance( $this->env );
		$this->logic->setContext(
			$this->session->get( 'auth_user_id' ),
			$this->session->get( 'auth_role_id' ),
			$this->session->get( 'filter_notes_projectId' )
		);
		$this->addData( 'logicNote', $this->logic );
	}
}
