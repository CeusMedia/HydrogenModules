<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Issue extends Hook
{
	static public function onRegisterTimerModule( Environment $env, $context, $module, $payload = [] )
	{
		$context->registerModule( (object) array(
			'moduleId'		=> 'Work_Issues',
			'typeLabel'		=> 'Problem',
			'modelClass'	=> 'Model_Issue',
			'linkDetails'	=> 'work/issue/edit/{id}',
		) );
	}

	static public function onRegisterDashboardPanels( Environment $env, $context, $module, $payload )
	{
		if( !$env->getAcl()->has( 'work/issue', 'ajaxRenderDashboardPanel' ) )
			return;
		$context->registerPanel( 'work-issues', array(
			'url'			=> 'work/issue/ajaxRenderDashboardPanel',
			'title'			=> 'offene Probleme',
			'heading'		=> 'offene Probleme',
			'icon'			=> 'fa fa-fw fa-exclamation',
			'rank'			=> 20,
		) );
	}

	static public function onProjectRemove( Environment $env, $context, $module, $payload )
	{
		$projectId	= $payload['projectId'];
		$model		= new Model_Issue( $env );
		$logic		= new Logic_Issue( $env );
		foreach( $model->getAllByIndex( 'projectId', $projectId ) as $issue ){
			$logic->remove( $issue->issueId );
		}
	}

	/**
	 *	@todo 		maybe reassign issues etc. instead of removing them (as already (partly) implemented for managed issues)
	 */
	static public function onUserRemove( Environment $env, $context, $module, $payload )
	{
		$payload	= (object) $payload;
		if( empty( $payload->userId ) ){
			$message	= 'Hook "Work_Issues::onUserRemove" is missing user ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$logic			= Logic_Issue::getInstance( $env );
		$modelIssue		= new Model_Issue( $env );
		$modelChange	= new Model_Issue_Change( $env );
		$modelNote		= new Model_Issue_Note( $env );

		$reportedIssues	= $modelIssue->getAllByIndex( 'reporterId', $payload->userId );
		foreach( $reportedIssues as $reportedIssue )
			$logic->remove( $reportedIssue->issueId );

		//  @todo		problem: what if manager is reporter?
		$managedIssues	= $modelIssue->getAllByIndex( 'managerId', $payload->userId );
		foreach( $managedIssues as $managedIssue )
			$modelIssue->edit( $managedIssue->issueId, array(
				'managerId'	=> $managedIssue->reporterId,
			) );

		$changes	= $modelChange->getAllByIndex( 'userId', $payload->userId );
		foreach( $changes as $change )
			$modelNote->remove( $change->issueChangeId );

		$notes		= $modelNote->getAllByIndex( 'userId', $payload->userId );
		foreach( $notes as $note )
			$modelNote->remove( $note->issueNoteId );

		if( isset( $payload->counts ) )
			$payload->counts['Work_Issues']	= (object) array(
				'entities'	=> count( $reportedIssues ) + count( $managedIssues ) + count( $changes ) + count( $notes ),
			);
	}

	static public function onListUserRelations( Environment $env, $context, $module, $payload )
	{
		$payload	= (object) $payload;
		if( empty( $payload->userId ) ){
			$message	= 'Hook "Work_Issues::onListUserRelations" is missing user ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$modelIssue		= new Model_Issue( $env );

		$activeOnly		= isset( $payload->activeOnly ) ? $payload->activeOnly : FALSE;
		$linkable		= isset( $payload->linkable ) ? $payload->linkable : FALSE;
		$statusesActive	= [0, 1, 2, 3, 4, 5];
		$list			= [];
		$indices		= ['reporterId' => $payload->userId];
		if( $activeOnly )
			$indices['status']	= $statusesActive;
		$orders			= ['type' => 'ASC', 'title' => 'ASC'];
		$icons			= array(
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-exclamation', 'title' => 'Fehler'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-wrench', 'title' => 'Aufgabe'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-lightbulb-o', 'title' => 'Wunsch/Idee'] ),
		);
		$words			= $env->getLanguage()->getWords( 'work/issue' );
		$reportedIssues	= $modelIssue->getAll( $indices, $orders );
		foreach( $reportedIssues as $issue ){
			$icon		= $icons[$issue->type];
			$isOpen		= in_array( $issue->status, $statusesActive );
			$status		= '('.$words['states'][$issue->status].')';
			$status		= HtmlTag::create( 'small', $status, ['class' => 'muted'] );
			$title		= $isOpen ? $issue->title : HtmlTag::create( 'del', $issue->title );
			$label		= $icon.'&nbsp;'.$title.'&nbsp;'.$status;
			$list[]		= (object) array(
				'id'		=> $payload->linkable ? $issue->issueId : NULL,
				'label'		=> $label,
			);
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$payload,																				//  hook content data
			$module,																				//  module called by hook
			'entity',																				//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Work_Issue',																			//  controller of entity
			'edit'																					//  action to view or edit entity
		);
	}

	static public function onListProjectRelations( Environment $env, $context, $module, $payload )
	{
		$modelProject	= new Model_Project( $env );
		if( empty( $payload->projectId ) ){
			$message	= 'Hook "Work_Issues::onListProjectRelations" is missing project ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		if( !( $project = $modelProject->get( $payload->projectId ) ) ){
			$message	= 'Hook "Work_Issues::onListProjectRelations": Invalid project ID.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$payload->activeOnly	= isset( $payload->activeOnly ) ? $payload->activeOnly : FALSE;
		$payload->linkable		= isset( $payload->linkable ) ? $payload->linkable : FALSE;
		$language		= $env->getLanguage();
		$statusesActive	= [0, 1, 2, 3, 4, 5];
		$list			= [];
		$modelIssue		= new Model_Issue( $env );
		$indices		= ['projectId' => $payload->projectId];
		if( $payload->activeOnly )
			$indices['status']	= $statusesActive;
		$orders			= ['type' => 'ASC', 'title' => 'ASC'];
		$issues			= $modelIssue->getAllByIndices( $indices, $orders );	//  ...
		$icons			= array(
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-exclamation', 'title' => 'Fehler'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-wrench', 'title' => 'Aufgabe'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-lightbulb-o', 'title' => 'Wunsch/Idee'] ),
		);
		$words		= $language->getWords( 'work/issue' );
		foreach( $issues as $issue ){
			$icon		= $icons[$issue->type];
			$isOpen		= in_array( $issue->status, $statusesActive );
			$status		= '('.$words['states'][$issue->status].')';
			$status		= HtmlTag::create( 'small', $status, ['class' => 'muted'] );
			$title		= $isOpen ? $issue->title : HtmlTag::create( 'del', $issue->title );
			$label		= $icon.'&nbsp;'.$title.'&nbsp;'.$status;
			$list[]		= (object) array(
				'id'		=> $payload->linkable ? $issue->issueId : NULL,
				'label'		=> $label,
			);
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$payload,																				//  hook content data
			$module,																				//  module called by hook
			'entity',																				//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Work_Issue',																			//  controller of entity
			'edit'																					//  action to view or edit entity
		);
	}
}
