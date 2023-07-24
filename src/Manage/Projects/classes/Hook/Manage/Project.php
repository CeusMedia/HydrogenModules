<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Project extends Hook
{
	static public function onGetRelatedUsers( Environment $env, $context, $module, $data )
	{
		$modelUser			= new Model_User( $env );
		$modelProjectUser	= new Model_Project_User( $env );
		$projectIds			= [];
		$userIds			= [-1];
		$myProjects			= $modelProjectUser->getAll( ['userId' => $data->userId] );
		foreach( $myProjects as $relation )
			$projectIds[]   = $relation->projectId;
		if( !$projectIds )
			return;
		$logic				= Logic_Project::getInstance( $env );
		$users				= $logic->getProjectsUsers( array_unique( $projectIds ), ['status' => '> 0'] );
//		unset( $users[$data->userId] );
		$words				= $env->getLanguage()->getWords( 'manage/project' );
		$data->list[]		= (object) array(
			'module'		=> 'Manage_Projects',
			'label'			=> $words['hook-getRelatedUsers']['label'],
			'count'			=> count( $users ),
			'list'			=> $users,
		);
	}

	static public function onUpdate( Environment $env, $context, $module, $data = [] )
	{
		if( empty( $data['projectId'] ) )
			throw new InvalidArgumentException( 'Missing project ID' );
		$model	= new Model_Project( $env );
		$model->edit( $data['projectId'], array( 'modifiedAt' => time() ) );
	}

	static public function onProjectRemove( Environment $env, $context, $module, $data )
	{
		$projectId	= $data['projectId'];
		$modelProject	= new Model_Project( $env );
		$modelUsers		= new Model_Project_User( $env );
		$modelUsers->removeByIndices( ['projectId' => $projectId] );
		$modelProject->remove( $projectId );
	}

	static public function onUserRemove( Environment $env, $context, $module, $data )
	{
		$data	= (object) $data;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Project::onUserRemove" is missing user ID in data';
			$env->getMessenger()->noteFailure( $message );
			return;
		}

		$logic			= Logic_Project::getInstance( $env );
		$modelRelation	= new Model_Project_User( $env );
		$projects		= $logic->getUserProjects( $data->userId, FALSE );

		$lists	= (object) ['entities' => [], 'relations' => []];
		foreach( $projects as $project ){
			$modelRelation->removeByIndices( [
				'projectId'	=> $project->projectId,
				'userId'	=> $data->userId
			] );
			$lists->relations[]	= $project;
			$users		= $logic->getProjectUsers( $project->projectId );
			if( count( $users ) === 0 ){
				$lists->entities[]	= $project;
				$payload	= ['projectId' => $project->projectId];
				$env->getCaptain()->callHook( 'Project', 'remove', $context, $payload );
				$modelProject	= new Model_Project( $env );
				$modelRelation	= new Model_Project_User( $env );
				$modelRelation->removeByIndex( 'projectId', $project->projectId );
				$modelProject->remove( $project->projectId );
			}
		}
		if( isset( $data->counts ) )
			$data->counts['Manage_Project']	= (object) array(
				'entities'	=> count( $lists->entities ),
				'relations'	=> count( $lists->relations ),
			);
/*		if( $lists->entities )
			$env->getMessenger()->noteSuccess( 'Removed %d projects.', count( $lists->entities ) );
		if( $lists->relations )
			$env->getMessenger()->noteSuccess( 'Removed %d project relations.', count( $lists->relations ) );
*/	}

	static public function onListUserRelations( Environment $env, $context, $module, $data )
	{
		$data	= (object) $data;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Project::onListRelations" is missing user ID in data';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$logic			= Logic_Project::getInstance( $env );
		$words			= $env->getLanguage()->getWords( 'manage/project' );
		$projects		= $logic->getUserProjects( $data->userId, FALSE );
		$icon			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cube'] );

		$lists	= (object) ['entities' => [], 'relations'	=> []];
		foreach( $projects as $project ){
			$users		= $logic->getProjectUsers( $project->projectId );
			$item		= (object) [
				'id'		=> $data->linkable ? $project->projectId : NULL,
				'label'		=> $icon.'&nbsp;'.$project->title,
			];
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

	static public function onListRelations( Environment $env, $context, $module, $data )
	{
		if( empty( $data->projectId ) ){
			$message	= 'Hook "Project::onListRelations" is missing project ID in data';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$modelProject	= new Model_Project( $env );
		$words			= $env->getLanguage()->getWords( 'manage/project' );

		if( !( $project = $modelProject->get( $data->projectId ) ) ){
			$message	= 'Hook "Work_Missions::onListProjectRelations": Invalid project ID.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$data->activeOnly	= isset( $data->activeOnly ) ? $data->activeOnly : FALSE;
		$data->linkable		= isset( $data->linkable ) ? $data->linkable : FALSE;

		$modelUser			= new Model_User( $env );

		$conditions		= [];
		if( $data->activeOnly )
			$conditions['status']	= 1;

		$logic			= Logic_Project::getInstance( $env );
		$projectUsers	= $logic->getProjectUsers( $data->projectId, $conditions, ['username' => 'ASC'] );

		$list				= [];
		$iconUser			= HtmlTag::create( 'i', '', ['class' => 'not_icon-user fa fa-fw fa-user'] );
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
				$fullname	= HtmlTag::create( 'small', $fullname, ['class' => 'muted'] );
				$link		= HtmlTag::create( 'a', $iconUser.'&nbsp;'.$user->username.'&nbsp;'.$fullname, [
					'href'	=> 'member/view/'.$user->userId,
				] );
			}
			$list[]		= (object) [
				'id'		=> $data->linkable ? $user->userId : NULL,
				'label'		=> $link,
			];
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
