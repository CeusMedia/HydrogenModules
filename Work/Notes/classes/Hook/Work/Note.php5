<?php
class Hook_Work_Note /*extends CMF_Hydrogen_Hook*/{

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= Logic_Note::getInstance( $this->env );
		$this->logic->setContext(
			$this->session->get( 'userId' ),
			$this->session->get( 'roleId' ),
			$this->session->get( 'filter_notes_projectId' )
		);
		$this->addData( 'logicNote', $this->logic );
	}

	static public function onProjectRemove( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$projectId	= $data['projectId'];
		$model		= new Model_Note( $env );
		$logic		= Logic_Note::getInstance( $env );
		foreach( $model->getAllByIndex( 'projectId', $projectId ) as $note ){
			$logic->removeNote( $note->noteId );
		}
	}

	static public function onListProjectRelations( CMF_Hydrogen_Environment $env, $context, $module, $data ){
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
//		$statusesActive	= array( 0, 1, 2, 3, 4, 5 );
		$list			= array();
		$modelNote		= new Model_Note( $env );
		$indices		= array( 'projectId' => $data->projectId );
//		if( $data->activeOnly )
//			$indices['status']	= $statusesActive;
		$orders			= array( 'status' => 'ASC', 'title' => 'ASC' );
		$notes			= $modelNote->getAllByIndices( $indices, $orders );	//  ...
/*		$icons			= array(
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-exclamation' ) ),
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-wrench' ) ),
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-lightbulb-o' ) ),
		);*/
		$icon		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sticky-note-o', 'title' => 'Notiz' ) );
		$words		= $language->getWords( 'work/note' );
		foreach( $notes as $note ){
			$isOpen		= TRUE;//in_array( $issue->status, $statusesActive );
//			$status		= '('.$words['states'][$issue->status].')';
//			$status		= UI_HTML_Tag::create( 'small', $status, array( 'class' => 'muted' ) );
			$title		= $isOpen ? $note->title : UI_HTML_Tag::create( 'del', $note->title );
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

	static public function onUserRemove( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$data		= (object) $data;
		$userId		= $data->userId;
		$model		= new Model_Note( $env );
		$logic		= Logic_Note::getInstance( $env );
		foreach( $model->getAllByIndex( 'userId', $userId ) as $note )
			$logic->removeNote( $note->noteId );
	}

	static public function onListUserRelations( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$data		= (object) $data;
		$userId		= $data->userId;
		$model		= new Model_Note( $env );
		$logic		= Logic_Note::getInstance( $env );
		$notes		= $model->getAllByIndex( 'userId', $userId );
		$language	= $env->getLanguage();
		$words		= $language->getWords( 'work/note' );
		$icon		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sticky-note-o', 'title' => 'Notiz' ) );
		foreach( $notes as $note ){
			$isOpen		= TRUE;//in_array( $issue->status, $statusesActive );
//			$status		= '('.$words['states'][$issue->status].')';
//			$status		= UI_HTML_Tag::create( 'small', $status, array( 'class' => 'muted' ) );
			$title		= $isOpen ? $note->title : UI_HTML_Tag::create( 'del', $note->title );
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
}
?>