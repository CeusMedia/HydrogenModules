<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Issue extends Hook
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onRegisterTimerModule(): void
	{
		$this->context->registerModule( (object) [
			'moduleId'		=> 'Work_Issues',
			'typeLabel'		=> 'Problem',
			'modelClass'	=> 'Model_Issue',
			'linkDetails'	=> 'work/issue/edit/{id}',
		] );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onRegisterDashboardPanels(): void
	{
		if( !$this->env->getAcl()->has( 'work/issue', 'ajaxRenderDashboardPanel' ) )
			return;
		$this->context->registerPanel( 'work-issues', [
			'url'			=> 'work/issue/ajaxRenderDashboardPanel',
			'title'			=> 'offene Probleme',
			'heading'		=> 'offene Probleme',
			'icon'			=> 'fa fa-fw fa-exclamation',
			'rank'			=> 20,
		] );
	}

	/**
	 *	@return		void
	 */
	public function onProjectRemove(): void
	{
		$projectId	= $this->payload['projectId'];
		$model		= new Model_Issue( $this->env );
		$logic		= new Logic_Issue( $this->env );
		foreach( $model->getAllByIndex( 'projectId', $projectId ) as $issue ){
			$logic->remove( $issue->issueId );
		}
	}

	/**
	 *	@return		void
	 */
	public function onListUserRelations(): void
	{
		$payload	= (object) $this->payload;
		if( empty( $payload->userId ) ){
			$message	= 'Hook "Work_Issues::onListUserRelations" is missing user ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		$modelIssue		= new Model_Issue( $this->env );

		$activeOnly		= $payload->activeOnly ?? FALSE;
		$linkable		= $payload->linkable ?? FALSE;
		$statusesActive	= [0, 1, 2, 3, 4, 5];
		$list			= [];
		$indices		= ['reporterId' => $payload->userId];
		if( $activeOnly )
			$indices['status']	= $statusesActive;
		$orders			= ['type' => 'ASC', 'title' => 'ASC'];
		$icons			= [
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-exclamation', 'title' => 'Fehler'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-wrench', 'title' => 'Aufgabe'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-lightbulb-o', 'title' => 'Wunsch/Idee'] ),
		];
		$words			= $this->env->getLanguage()->getWords( 'work/issue' );
		$reportedIssues	= $modelIssue->getAll( $indices, $orders );
		foreach( $reportedIssues as $issue ){
			$icon		= $icons[$issue->type];
			$isOpen		= in_array( $issue->status, $statusesActive );
			$status		= '('.$words['states'][$issue->status].')';
			$status		= HtmlTag::create( 'small', $status, ['class' => 'muted'] );
			$title		= $isOpen ? $issue->title : HtmlTag::create( 'del', $issue->title );
			$label		= $icon.'&nbsp;'.$title.'&nbsp;'.$status;
			$list[]		= (object) [
				'id'		=> $payload->linkable ? $issue->issueId : NULL,
				'label'		=> $label,
			];
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$payload,																				//  hook content data
			$this->module,																			//  module called by hook
			'entity',																			//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Work_Issue',																	//  controller of entity
			'edit'																			//  action to view or edit entity
		);
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@todo 		maybe reassign issues etc. instead of removing them (as already (partly) implemented for managed issues)
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onUserRemove(): void
	{
		$payload	= (object) $this->payload;
		if( empty( $payload->userId ) ){
			$message	= 'Hook "Work_Issues::onUserRemove" is missing user ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		/** @var Logic_Issue $logic */
		$logic			= Logic_Issue::getInstance( $this->env );
		$modelIssue		= new Model_Issue( $this->env );
		$modelChange	= new Model_Issue_Change( $this->env );
		$modelNote		= new Model_Issue_Note( $this->env );

		$reportedIssues	= $modelIssue->getAllByIndex( 'reporterId', $payload->userId );
		foreach( $reportedIssues as $reportedIssue )
			$logic->remove( $reportedIssue->issueId );

		//  @todo		problem: what if manager is reporter?
		$managedIssues	= $modelIssue->getAllByIndex( 'managerId', $payload->userId );
		foreach( $managedIssues as $managedIssue )
			$modelIssue->edit( $managedIssue->issueId, [
				'managerId'	=> $managedIssue->reporterId,
			] );

		$changes	= $modelChange->getAllByIndex( 'userId', $payload->userId );
		foreach( $changes as $change )
			$modelNote->remove( $change->issueChangeId );

		$notes		= $modelNote->getAllByIndex( 'userId', $payload->userId );
		foreach( $notes as $note )
			$modelNote->remove( $note->issueNoteId );

		if( isset( $payload->counts ) )
			$payload->counts['Work_Issues']	= (object) [
				'entities'	=> count( $reportedIssues ) + count( $managedIssues ) + count( $changes ) + count( $notes ),
			];
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onListProjectRelations(): void
	{
		$modelProject	= new Model_Project( $this->env );
		if( empty( $payload->projectId ) ){
			$message	= 'Hook "Work_Issues::onListProjectRelations" is missing project ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		if( !$modelProject->has( $payload->projectId ) ){
			$message	= 'Hook "Work_Issues::onListProjectRelations": Invalid project ID.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		$payload->activeOnly	= $payload->activeOnly ?? FALSE;
		$payload->linkable		= $payload->linkable ?? FALSE;
		$language		= $this->env->getLanguage();
		$statusesActive	= [0, 1, 2, 3, 4, 5];
		$list			= [];
		$modelIssue		= new Model_Issue( $this->env );
		$indices		= ['projectId' => $payload->projectId];
		if( $payload->activeOnly )
			$indices['status']	= $statusesActive;
		$orders			= ['type' => 'ASC', 'title' => 'ASC'];
		$issues			= $modelIssue->getAllByIndices( $indices, $orders );	//  ...
		$icons			= [
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-exclamation', 'title' => 'Fehler'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-wrench', 'title' => 'Aufgabe'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-lightbulb-o', 'title' => 'Wunsch/Idee'] ),
		];
		$words		= $language->getWords( 'work/issue' );
		foreach( $issues as $issue ){
			$icon		= $icons[$issue->type];
			$isOpen		= in_array( $issue->status, $statusesActive );
			$status		= '('.$words['states'][$issue->status].')';
			$status		= HtmlTag::create( 'small', $status, ['class' => 'muted'] );
			$title		= $isOpen ? $issue->title : HtmlTag::create( 'del', $issue->title );
			$label		= $icon.'&nbsp;'.$title.'&nbsp;'.$status;
			$list[]		= (object) [
				'id'		=> $payload->linkable ? $issue->issueId : NULL,
				'label'		=> $label,
			];
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$payload,																				//  hook content data
			$this->module,																			//  module called by hook
			'entity',																			//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Work_Issue',																	//  controller of entity
			'edit'																			//  action to view or edit entity
		);
	}
}
