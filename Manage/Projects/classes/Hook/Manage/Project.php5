<?php
class Hook_Manage_Project /*extends CMF_Hydrogen_Hook*/{

	static public function onGetRelatedUsers( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$modelUser			= new Model_User( $env );
		$modelProjectUser	= new Model_Project_User( $env );
		$projectIds			= array();
		$userIds			= array( -1 );
		$myProjects			= $modelProjectUser->getAll( array( 'userId' => $data->userId ) );
		foreach( $myProjects as $relation )
			$projectIds[]   = $relation->projectId;
		if( !$projectIds )
			return;
		$logic				= Logic_Project::getInstance( $env );
		$users				= $logic->getProjectsUsers( array_unique( $projectIds ), array( 'status' => '>0' ) );
//		unset( $users[$data->userId] );
		$words				= $env->getLanguage()->getWords( 'manage/project' );
		$data->list[]		= (object) array(
			'module'		=> 'Manage_Projects',
			'label'			=> $words['hook-getRelatedUsers']['label'],
			'count'			=> count( $users ),
			'list'			=> $users,
		);
	}

	static public function onUpdate( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( empty( $data['projectId'] ) )
			throw new InvalidArgumentException( 'Missing project ID' );
		$model	= new Model_Project( $env );
		$model->edit( $data['projectId'], array( 'modifiedAt' => time() ) );
	}

	static public function onProjectRemove( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$projectId	= $data['projectId'];
		$model		= new Model_Project_User( $env );
		$model->removeByIndices( array( 'projectId' => $projectId ) );
	}

	static public function onUserRemove( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$data	= (object) $data;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Project::___onUserRemove" is missing user ID in data';
			$env->getMessenger()->noteFailure( $message );
			return;
		}

		$logic			= Logic_Project::getInstance( $env );
		$projects		= $logic->getUserProjects( $data->userId, FALSE );

		$lists	= (object) array( 'entities' => array(), 'relations' => array() );
		foreach( $projects as $project ){
			$users		= $logic->getProjectUsers( $project->projectId );
			if( count( $users ) === 1 && isset( $users[$data->userId] ) ){								//  no other users in project
				$lists->entities[]	= $project;
//				$env->getCaptain()->callHook( 'Project', 'remove', $context, array( 'projectId' => $project->projectId ) );
			}
			else{
				$lists->relations[]	= $project;
//				$logic->removeProjectUser( $project->projectId, $data->userId, TRUE );
			}
		}
		if( $lists->entities )
			$env->getMessenger()->noteSuccess( 'Removed %d projects.', count( $lists->entities ) );
		if( $lists->relations )
			$env->getMessenger()->noteSuccess( 'Removed %d project relations.', count( $lists->relations ) );
	}

	static public function onListUserRelations( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$data	= (object) $data;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Project::___onListRelations" is missing user ID in data';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$logic			= Logic_Project::getInstance( $env );
		$words			= $env->getLanguage()->getWords( 'manage/project' );
		$projects		= $logic->getUserProjects( $data->userId, FALSE );
		$icon			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cube' ) );

		$lists	= (object) array( 'entities' => array(), 'relations'	=> array() );
		foreach( $projects as $project ){
			$users		= $logic->getProjectUsers( $project->projectId );
			$item		= (object) array(
				'id'		=> $data->linkable ? $project->projectId : NULL,
				'label'		=> $icon.'&nbsp;'.$project->title,
			);
			if( count( $users ) === 1 && isset( $users[$data->userId] ) ){								//  no other users in project
				$lists->entities[]	= $item;
			}
			else{
				$lists->relations[]	= $item;
			}
		}
		if( $lists->entities )
			View_Helper_ItemRelationLister::enqueueRelations(
				$data,																					//  hook content data
				$module,																				//  module called by hook
				'entity',																				//  relation type: entity or relation
				$lists->entities,																					//  list of related items
				$words['hook-relations']['labelProjects'],												//  label of type of related items
				'Manage_Project',																		//  controller of entity
				'view'																					//  action to view or edit entity
			);
		if( $lists->relations )
			View_Helper_ItemRelationLister::enqueueRelations(
				$data,																					//  hook content data
				$module,																				//  module called by hook
				'relation',																				//  relation type: entity or relation
				$lists->relations,																		//  list of related items
				$words['hook-relations']['labelProjectRelations'],										//  label of type of related items
				'Manage_Project',																		//  controller of entity
				'view'																					//  action to view or edit entity
			);
	}

	static public function onListRelations( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		if( empty( $data->projectId ) ){
			$message	= 'Hook "Project::___onListRelations" is missing project ID in data';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$modelProject	= new Model_Project( $env );
		$words			= $env->getLanguage()->getWords( 'manage/project' );

		if( !( $project = $modelProject->get( $data->projectId ) ) ){
			$message	= 'Hook "Work_Missions::___onListProjectRelations": Invalid project ID.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$data->activeOnly	= isset( $data->activeOnly ) ? $data->activeOnly : FALSE;
		$data->linkable		= isset( $data->linkable ) ? $data->linkable : FALSE;

		$modelUser			= new Model_User( $env );

		$conditions		= array();
		if( $data->activeOnly )
			$conditions['status']	= 1;

		$logic			= Logic_Project::getInstance( $env );
		$projectUsers	= $logic->getProjectUsers( $data->projectId, $conditions, array( 'username' => 'ASC' ) );

		$list				= array();
		$iconUser			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'not_icon-user fa fa-fw fa-user' ) );
		foreach( $projectUsers as $user ){
			if( $env->getModules()->has( 'Members' ) ){
				$helper	= new View_Helper_Member( $env );
				$helper->setUser( $user );
				$helper->setMode( 'inline' );
				$helper->setLinkUrl( 'member/view/'.$user->userId );
				$link	= $helper->render();
			}
			else{
				$fullname	= '('.$user->firstname.' '.$user->surname.')';
				$fullname	= UI_HTML_Tag::create( 'small', $fullname, array( 'class' => 'muted' ) );
				$link		= UI_HTML_Tag::create( 'a', $iconUser.'&nbsp;'.$user->username.'&nbsp;'.$fullname, array(
					'href'	=> 'member/view/'.$user->userId,
				) );
			}
			$list[]		= (object) array(
				'id'		=> $data->linkable ? $user->userId : NULL,
				'label'		=> $link,
			);
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$data,																					//  hook content data
			$module,																				//  module called by hook
			'relation',																				//  relation type: entity or relation
			$list,																					//  list of related items
			'Projekt-Teilnehmer',																	//  label of type of related items
			'Manage_User',																			//  controller of entity
			'edit'																					//  action to view or edit entity
		);
	}

}
?>
