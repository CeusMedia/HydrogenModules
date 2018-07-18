<?php
class Hook_Work_Issue /*extends CMF_Hydrogen_Hook*/{

	static public function onRegisterTimerModule( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$context->registerModule( (object) array(
			'moduleId'		=> 'Work_Issues',
			'typeLabel'		=> 'Problem',
			'modelClass'	=> 'Model_Issue',
			'linkDetails'	=> 'work/issue/edit/{id}',
		) );
	}

	static public function onRegisterDashboardPanels( CMF_Hydrogen_Environment $env, $context, $module, $data ){
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

	static public function onProjectRemove( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$projectId	= $data['projectId'];
		$model		= new Model_Issue( $env );
		$logic		= new Logic_Issue( $env );
		foreach( $model->getAllByIndex( 'projectId', $projectId ) as $issue ){
			$logic->remove( $issue->issueId );
		}
	}


	static public function onUserRemove( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$data	= (object) $data;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Work_Issues::onUserRemove" is missing user ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$logic			= Logic_Issue::getInstance( $env );
		$modelIssue		= new Model_Issue( $env );
		$modelChange	= new Model_Issue_Change( $env );
		$modelNote		= new Model_Issue_Note( $env );

		$reportedIssues	= $modelIssue->getAllByIndex( 'reporterId', $data->userId );
		foreach( $reportedIssues as $reportedIssue )
			$logic->remove( $reportedIssue->issueId );

		$managedIssues	= $modelIssue->getAllByIndex( 'managerId', $data->userId );
		foreach( $managedIssues as $managedIssue )
			$modelIssue->edit( $managedIssue->issueId, array(
				'managerId'	=> $managedIssue->reporterId,
			) );

		foreach( $modelChange->getByIndex( 'userId', $data->userId ) as $change )
			$modelNote->remove( $change->issueChangeId );

		foreach( $modelNote->getByIndex( 'userId', $data->userId ) as $note )
			$modelNote->remove( $note->issueNoteId );
	}

	static public function onListUserRelations( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$data	= (object) $data;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Work_Issues::onListUserRelations" is missing user ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$modelIssue		= new Model_Issue( $env );

		$activeOnly		= isset( $data->activeOnly ) ? $data->activeOnly : FALSE;
		$linkable		= isset( $data->linkable ) ? $data->linkable : FALSE;
		$statusesActive	= array( 0, 1, 2, 3, 4, 5 );
		$list			= array();
		$indices		= array( 'reporterId' => $data->userId );
		if( $activeOnly )
			$indices['status']	= $statusesActive;
		$orders			= array( 'type' => 'ASC', 'title' => 'ASC' );
		$icons			= array(
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-exclamation', 'title' => 'Fehler' ) ),
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-wrench', 'title' => 'Aufgabe' ) ),
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-lightbulb-o', 'title' => 'Wunsch/Idee' ) ),
		);
		$words			= $env->getLanguage()->getWords( 'work/issue' );
		$reportedIssues	= $modelIssue->getAll( $indices, $orders );
		foreach( $reportedIssues as $issue ){
			$icon		= $icons[$issue->type];
			$isOpen		= in_array( $issue->status, $statusesActive );
			$status		= '('.$words['states'][$issue->status].')';
			$status		= UI_HTML_Tag::create( 'small', $status, array( 'class' => 'muted' ) );
			$title		= $isOpen ? $issue->title : UI_HTML_Tag::create( 'del', $issue->title );
			$label		= $icon.'&nbsp;'.$title.'&nbsp;'.$status;
			$list[]		= (object) array(
				'id'		=> $data->linkable ? $issue->issueId : NULL,
				'label'		=> $label,
			);
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$data,																					//  hook content data
			$module,																				//  module called by hook
			'entity',																				//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Work_Issue',																			//  controller of entity
			'edit'																					//  action to view or edit entity
		);
	}

	static public function onListProjectRelations( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$modelProject	= new Model_Project( $env );
		if( empty( $data->projectId ) ){
			$message	= 'Hook "Work_Issues::onListProjectRelations" is missing project ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		if( !( $project = $modelProject->get( $data->projectId ) ) ){
			$message	= 'Hook "Work_Issues::onListProjectRelations": Invalid project ID.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$data->activeOnly	= isset( $data->activeOnly ) ? $data->activeOnly : FALSE;
		$data->linkable		= isset( $data->linkable ) ? $data->linkable : FALSE;
		$language		= $env->getLanguage();
		$statusesActive	= array( 0, 1, 2, 3, 4, 5 );
		$list			= array();
		$modelIssue		= new Model_Issue( $env );
		$indices		= array( 'projectId' => $data->projectId );
		if( $data->activeOnly )
			$indices['status']	= $statusesActive;
		$orders			= array( 'type' => 'ASC', 'title' => 'ASC' );
		$issues			= $modelIssue->getAllByIndices( $indices, $orders );	//  ...
		$icons			= array(
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-exclamation', 'title' => 'Fehler' ) ),
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-wrench', 'title' => 'Aufgabe' ) ),
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-lightbulb-o', 'title' => 'Wunsch/Idee' ) ),
		);
		$words		= $language->getWords( 'work/issue' );
		foreach( $issues as $issue ){
			$icon		= $icons[$issue->type];
			$isOpen		= in_array( $issue->status, $statusesActive );
			$status		= '('.$words['states'][$issue->status].')';
			$status		= UI_HTML_Tag::create( 'small', $status, array( 'class' => 'muted' ) );
			$title		= $isOpen ? $issue->title : UI_HTML_Tag::create( 'del', $issue->title );
			$label		= $icon.'&nbsp;'.$title.'&nbsp;'.$status;
			$list[]		= (object) array(
				'id'		=> $data->linkable ? $issue->issueId : NULL,
				'label'		=> $label,
			);
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$data,																					//  hook content data
			$module,																				//  module called by hook
			'entity',																				//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Work_Issue',																			//  controller of entity
			'edit'																					//  action to view or edit entity
		);
	}
}
