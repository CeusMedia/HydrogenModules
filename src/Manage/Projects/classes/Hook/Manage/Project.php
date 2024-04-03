<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Project extends Hook
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onGetRelatedUsers(): void
	{
		$modelUser			= new Model_User( $this->env );
		$modelProjectUser	= new Model_Project_User( $this->env );
		$projectIds			= [];
		$userIds			= [-1];
		$myProjects			= $modelProjectUser->getAll( ['userId' => $this->payload['userId']] );
		foreach( $myProjects as $relation )
			$projectIds[]   = $relation->projectId;
		if( !$projectIds )
			return;
		$logic				= Logic_Project::getInstance( $this->env );
		$users				= $logic->getProjectsUsers( array_unique( $projectIds ), ['status' => '> 0'] );
//		unset( $users[$payload['userId']] );
		$words				= $this->env->getLanguage()->getWords( 'manage/project' );
		$this->payload['list'][]		= (object) [
			'module'		=> 'Manage_Projects',
			'label'			=> $words['hook-getRelatedUsers']['label'],
			'count'			=> count( $users ),
			'list'			=> $users,
		];
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onUpdate(): void
	{
		if( empty( $this->payload['projectId'] ) )
			throw new InvalidArgumentException( 'Missing project ID' );
		$model	= new Model_Project( $this->env );
		$model->edit( $this->payload['projectId'], array( 'modifiedAt' => time() ) );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onProjectRemove(): void
	{
		$projectId	= $this->payload['projectId'];
		$modelProject	= new Model_Project( $this->env );
		$modelUsers		= new Model_Project_User( $this->env );
		$modelUsers->removeByIndices( ['projectId' => $projectId] );
		$modelProject->remove( $projectId );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onUserRemove(): void
	{
		if( empty( $this->payload['userId'] ) ){
			$message	= 'Hook "Project::onUserRemove" is missing user ID in data';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}

		$logic			= Logic_Project::getInstance( $this->env );
		$modelRelation	= new Model_Project_User( $this->env );
		$projects		= $logic->getUserProjects( $this->payload['userId'], FALSE );

		$lists	= (object) ['entities' => [], 'relations' => []];
		foreach( $projects as $project ){
			$modelRelation->removeByIndices( [
				'projectId'	=> $project->projectId,
				'userId'	=> $this->payload['userId']
			] );
			$lists->relations[]	= $project;
			$users		= $logic->getProjectUsers( $project->projectId );
			if( count( $users ) === 0 ){
				$lists->entities[]	= $project;
				$payload	= ['projectId' => $project->projectId];
				$this->env->getCaptain()->callHook( 'Project', 'remove', $this->context, $payload );
				$modelProject	= new Model_Project( $this->env );
				$modelRelation	= new Model_Project_User( $this->env );
				$modelRelation->removeByIndex( 'projectId', $project->projectId );
				$modelProject->remove( $project->projectId );
			}
		}
		if( isset( $this->payload['counts'] ) )
			$this->payload['counts']['Manage_Project']	= (object) [
				'entities'	=> count( $lists->entities ),
				'relations'	=> count( $lists->relations ),
			];
/*		if( $lists->entities )
			$env->getMessenger()->noteSuccess( 'Removed %d projects.', count( $lists->entities ) );
		if( $lists->relations )
			$env->getMessenger()->noteSuccess( 'Removed %d project relations.', count( $lists->relations ) );
*/	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onListUserRelations(): void
	{
		if( empty( $this->payload['userId'] ) ){
			$message	= 'Hook "Project::onListRelations" is missing user ID in data';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		$logic			= Logic_Project::getInstance( $this->env );
		$words			= $this->env->getLanguage()->getWords( 'manage/project' );
		$projects		= $logic->getUserProjects( $this->payload['userId'], FALSE );
		$icon			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cube'] );

		$lists	= (object) ['entities' => [], 'relations'	=> []];
		foreach( $projects as $project ){
			$users		= $logic->getProjectUsers( $project->projectId );
			$item		= (object) [
				'id'		=> $this->payload['linkable'] ? $project->projectId : NULL,
				'label'		=> $icon.'&nbsp;'.$project->title,
			];
			if( count( $users ) === 1 && isset( $users[$this->payload['userId']] ) ){								//  no other users in project
				$lists->entities[]	= $item;
			}
			else{
				$lists->relations[]	= $item;
			}
		}
		if( $lists->entities )
			View_Helper_ItemRelationLister::enqueueRelations(
				$this->payload,																					//  hook content data
				$this->module,																				//  module called by hook
				'entity',																				//  relation type: entity or relation
				$lists->entities,																					//  list of related items
				$words['hook-relations']['labelProjects'],												//  label of type of related items
				'Manage_Project',																		//  controller of entity
				'view'																					//  action to view or edit entity
			);
		if( $lists->relations )
			View_Helper_ItemRelationLister::enqueueRelations(
				$this->payload,																					//  hook content data
				$this->module,																				//  module called by hook
				'relation',																				//  relation type: entity or relation
				$lists->relations,																		//  list of related items
				$words['hook-relations']['labelProjectRelations'],										//  label of type of related items
				'Manage_Project',																		//  controller of entity
				'view'																					//  action to view or edit entity
			);
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onListRelations(): void
	{
		if( empty( $this->payload['projectId'] ) ){
			$message	= 'Hook "Project::onListRelations" is missing project ID in data';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		$modelProject	= new Model_Project( $this->env );
		$words			= $this->env->getLanguage()->getWords( 'manage/project' );

		if( !( $project = $modelProject->get( $this->payload['projectId'] ) ) ){
			$message	= 'Hook "Work_Missions::onListProjectRelations": Invalid project ID.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		$this->payload['activeOnly']	??= FALSE;
		$this->payload['linkable']		??= FALSE;

		$modelUser			= new Model_User( $this->env );

		$conditions		= [];
		if( $this->payload['activeOnly'] )
			$conditions['status']	= 1;

		$logic			= Logic_Project::getInstance( $this->env );
		$projectUsers	= $logic->getProjectUsers( $this->payload['projectId'], $conditions, ['username' => 'ASC'] );

		$list				= [];
		$iconUser			= HtmlTag::create( 'i', '', ['class' => 'not_icon-user fa fa-fw fa-user'] );
		foreach( $projectUsers as $user ){
			if( $this->env->getModules()->has( 'Members' ) ){
				$helper	= new View_Helper_Member( $this->env );
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
				'id'		=> $this->payload['linkable'] ? $user->userId : NULL,
				'label'		=> $link,
			];
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$this->payload,																					//  hook content data
			$this->module,																				//  module called by hook
			'relation',																				//  relation type: entity or relation
			$list,																					//  list of related items
			'Projekt-Teilnehmer',																	//  label of type of related items
			'Manage_User',																			//  controller of entity
			'edit'																					//  action to view or edit entity
		);
	}
}
