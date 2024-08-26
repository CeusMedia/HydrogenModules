<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Mission extends Hook
{
	static array $statusesActive	= [
		Model_Mission::STATUS_NEW,
		Model_Mission::STATUS_ACCEPTED,
		Model_Mission::STATUS_PROGRESS,
		Model_Mission::STATUS_READY,
	];

	/**
	 *	@return		void
	 */
	public function onCollectNovelties(): void
	{
		$model		= new Model_Mission_Document( $this->env );
		$conditions	= ['modifiedAt' => '> '.( time() - 30 * 24 * 60 * 60 )];
		$orders		= ['modifiedAt' => 'DESC'];
		foreach( $model->getAll( $conditions, $orders ) as $item ){
			$this->context->add( (object) [
				'module'	=> 'Work_Missions',
				'type'		=> 'document',
				'typeLabel'	=> 'Dokument',
				'id'		=> $item->missionDocumentId,
				'title'		=> $item->filename,
				'timestamp'	=> max( $item->createdAt, $item->modifiedAt ),
				'url'		=> './work/mission/downloadDocument/'.$item->missionId.'/'.$item->missionDocumentId,
			] );
		}
	}

	/**
	 *	@return		void
	 */
	public function onRegisterTimerModule(): void
	{
		$this->context->registerModule( (object) [
			'moduleId'		=> 'Work_Missions',
			'typeLabel'		=> 'Aufgabe',
			'modelClass'	=> 'Model_Mission',
			'linkDetails'	=> 'work/mission/view/{id}',
		] );
	}

	public function onDatabaseLockReleaseCheck()
	{
		$data	= (object) $this->payload;
		$controllerAction	= $data->controller.'/'.$data->action;
		$skipActions		= [
			'work/mission/export/ical',
			'work/mission/addDocument',
			'work/mission/edit',
			'work/time/add',
			'work/time/start',
			'work/time/pause',
			'work/time/stop',
		];
		if( in_array( $controllerAction, $skipActions ) )
			return FALSE;
		if( !$data->userId )
			return FALSE;
		$logicLock	= new Logic_Database_Lock( $this->env );
		$locks		= $logicLock->getUserLocks( $data->userId );
		foreach( $locks as $lock ){
			if( 'Work_Missions' === $lock->subject ){
//				error_log( time().": Missions:onDatabaseLockReleaseCheck: ".json_encode( $data->request->get( '__path') )."\n", 3, "unlock.log" );
				$logicLock->unlock( $lock->subject, $lock->entryId, $data->userId );
			}
		}
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onProjectRemove(): void
	{
		$data	= (object) $this->payload;
		$data->informOthers	= $data->informOthers ?? FALSE;
		if( empty( $data->projectId ) ){
			$message	= 'Hook "Work_Missions::onProjectRemove" is missing project ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		$modelProject	= new Model_Project( $this->env );
		if( NULL === $modelProject->get( $data->projectId ) ){
			$message	= 'Hook "Work_Missions::onProjectRemove": Invalid project ID.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		$logicMission	= Logic_Work_Mission::getInstance( $this->env );
		$modelMission	= new Model_Mission( $this->env );
		$missions		= $modelMission->getAllByIndex( 'projectId', $data->projectId );
		foreach( $missions as $mission ){
			$logicMission->removeMission( $mission->missionId );
		}
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onListProjectRelations(): void
	{
		$data			= (object) $this->payload;
		$modelProject	= new Model_Project( $this->env );
		if( empty( $data->projectId ) ){
			$message	= 'Hook "Work_Missions::onListProjectRelations" is missing project ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		if( NULL === $modelProject->get( $data->projectId ) ){
			$message	= 'Hook "Work_Missions::onListProjectRelations": Invalid project ID.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		$data->activeOnly	= $data->activeOnly ?? FALSE;
		$data->linkable		= $data->linkable ?? FALSE;

		$modelMission	= new Model_Mission( $this->env );
		$words			= $this->env->getLanguage()->getWords( 'work/mission' );

		$list			= [];
		$indices		= ['projectId' => $data->projectId];
		if( $data->activeOnly )
			$indices['status']	= self::$statusesActive;
		$orders			= ['type' => 'DESC', 'title' => 'ASC'];
		$missions		= $modelMission->getAllByIndices( $indices, $orders );	//  ...

		$icons			= [
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-thumb-tack'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-clock-o'] ),
		];
		foreach( $missions as $mission ){
			$icon		= $icons[$mission->type];
			$isOpen		= in_array( $mission->status, self::$statusesActive );
			$status		= '('.$words['states'][$mission->status].')';
			$status		= HtmlTag::create( 'small', $status, ['class' => 'muted'] );
			$title		= $isOpen ? $mission->title : HtmlTag::create( 'del', $mission->title );
			$label		= $icon.'&nbsp;'.$title.'&nbsp;'.$status;
			$list[]		= (object) [
				'id'		=> $data->linkable ? $mission->missionId : NULL,
				'label'		=> $label,
			];
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$data,																					//  hook content data
			$this->module,																			//  module called by hook
			'entity',																			//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Work_Mission',																//  controller of entity
			'edit'																			//  action to view or edit entity
		);
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onListUserRelations(): void
	{
		$data		= (object) $this->payload;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Work_Missions::onListUserRelations" is missing user ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		/** @var Logic_Project $logic */
		$logic			= Logic_Project::getInstance( $this->env );
		$words			= $this->env->getLanguage()->getWords( 'work/mission' );

		$projectIds		= [];
		$projects		= $logic->getUserProjects( $data->userId, FALSE );
		foreach( $projects as $project ){
			$users		= $logic->getProjectUsers( $project->projectId );
			if( count( $users ) !== 1 || !isset( $users[$data->userId] ) )						//  other users in project
				continue;
			$projectIds[]	= $project->projectId;
		}

		if( empty( $projectIds ) )
			return;
		$data->activeOnly	= $data->activeOnly ?? FALSE;
		$data->linkable		= $data->linkable ?? FALSE;
		$list			= [];
		$modelMission	= new Model_Mission( $this->env );
		$indices		= ['projectId' => $projectIds];
		if( $data->activeOnly )
			$indices['status']	= self::$statusesActive;
		$orders			= ['type' => 'DESC', 'title' => 'ASC'];

		$missions		= $modelMission->getAllByIndices( $indices, $orders );	//  ...
		$icons			= array(
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-thumb-tack'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-clock-o'] ),
		);
		foreach( $missions as $mission ){
			$icon		= $icons[$mission->type];
			$isOpen		= in_array( $mission->status, self::$statusesActive );
			$status		= '('.$words['states'][$mission->status].')';
			$status		= HtmlTag::create( 'small', $status, ['class' => 'muted'] );
			$title		= $isOpen ? $mission->title : HtmlTag::create( 'del', $mission->title );
			$label		= $icon.'&nbsp;'.$title.'&nbsp;'.$status;
			$list[]		= (object) [
				'id'		=> $data->linkable ? $mission->missionId : NULL,
				'label'		=> $label,
			];
		}
		if( $list )
			View_Helper_ItemRelationLister::enqueueRelations(
				$data,																			//  hook content data
				$this->module,																	//  module called by hook
				'entity',																	//  relation type: entity or relation
				$list,																			//  list of related items
				$words['hook-relations']['label'],												//  label of type of related items
				'Work_Mission',														//  controller of entity
				'edit'																	//  action to view or edit entity
			);
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onUserRemove(): void
	{
		$data				= (object) $this->payload;
		$data->informOthers	= $data->informOthers ?? FALSE;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Work_Missions::onUserRemove" is missing user ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		/** @var Logic_Project $logicProject */
		$logicProject	= Logic_Project::getInstance( $this->env );
		$logicMission	= Logic_Work_Mission::getInstance( $this->env );
		$modelMission	= new Model_Mission( $this->env );
		$modelFilter	= new Model_Mission_Filter( $this->env );
		$words			= $this->env->getLanguage()->getWords( 'work/mission' );
		$lists			= (object) ['entities' => [], 'relations' => []];

		$modelFilter->removeByIndex( 'userId', $data->userId );

		$nrMissionsRemoved	= 0;
		$nrMissionsChanged	= 0;
		$projects		= $logicProject->getUserProjects( $data->userId, FALSE );
		foreach( $projects as $project ){
			$users		= $logicProject->getProjectUsers( $project->projectId );
			$missions	= $modelMission->getAllByIndex( 'projectId', $project->projectId );
			if( 1 === count( $users ) && isset( $users[$data->userId] ) ){						//  no other users in project
				foreach( $missions as $mission ){
					$logicMission->removeMission( $mission->missionId );
					$nrMissionsRemoved++;
				}
				continue;
			}
			$nextUserId	= 0;
			foreach( $users as $itemUser ){
				if( $itemUser->userId != $data->userId ){
					$nextUserId	= $itemUser->userId;
					break;
				}
			}
			foreach( $missions as $mission ){
				$old	= clone $mission;
				$listProgressingMissionStatues	= [
					Model_Mission::STATUS_ACCEPTED,
					Model_Mission::STATUS_PROGRESS,
					Model_Mission::STATUS_READY,
					Model_Mission::STATUS_FINISHED,
				];
				if( $mission->creatorId == $data->userId )
					$mission->creatorId		= $nextUserId;
				if( $mission->workerId == $data->userId )
					$mission->workerId		= $mission->creatorId;
				if( $mission->modifierId == $data->userId )
					$mission->modifierId	= 0;
				if( $old != $mission ){
					$nrMissionsChanged++;
					$modelMission->edit( $mission->missionId, (array) $mission );
					if( $data->informOthers && in_array( $mission->status, $listProgressingMissionStatues ) )
						$logicMission->noteChange( 'update', $mission->missionId, $old, $data->userId );
				}
			}
		}
		if( $nrMissionsRemoved )
			$this->env->getMessenger()->noteSuccess( 'Removed %d missions.', $nrMissionsRemoved );
		if( $nrMissionsChanged )
			$this->env->getMessenger()->noteSuccess( 'Reassigned %d missions.', $nrMissionsChanged );
		if( isset( $data->counts ) )
			$data->counts['Work_Missions']	= (object) [
				'entities'		=> $nrMissionsRemoved,
				'relations'		=> $nrMissionsChanged,
			];
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onStartTimer(): void
	{
		$timer	= $this->payload['timer'];
		if( 'Work_Missions' === $timer->module && $timer->moduleId ){
			$model		= new Model_Mission( $this->env );
			$mission	= $model->get( $timer->moduleId );
			if( in_array( $mission->status, [
				Model_Mission::STATUS_ABORTED,
				Model_Mission::STATUS_REJECTED,
				Model_Mission::STATUS_NEW,
				Model_Mission::STATUS_ACCEPTED,
				Model_Mission::STATUS_READY,
				Model_Mission::STATUS_FINISHED
			] ) ){
				$model->edit( $timer->moduleId, ['status' => Model_Mission::STATUS_PROGRESS] );
			}
		}
	}

	public function onPauseTimer(): void
	{
//		self::___onStartTimer( $env, $context, $module, $data );
	}

	public function onStopTimer(): void
	{
//		self::___onStartTimer( $env, $context, $module, $data );
	}

	/**
	 *	@return		void
	 */
	public function onRegisterDashboardPanels(): void
	{
		$this->context->registerPanel( 'work-mission-my-today', [
			'url'		=> 'ajax/work/mission/renderDashboardPanel',
			'title'		=> 'Heute & Termine',
			'heading'	=> 'Heute & Termine',
			'icon'		=> 'fa fa-fw fa-calendar-o',
			'rank'		=> 10,
			'refresh'	=> 60,
		] );
		$this->context->registerPanel( 'work-mission-my-tasks', [
			'url'		=> 'ajax/work/mission/renderDashboardPanel',
			'title'		=> 'Aufgaben: Meine - Heute',
			'heading'	=> 'Meine heutigen Aufgaben',
			'icon'		=> 'fa fa-fw fa-thumb-tack',
			'rank'		=> 20,
			'refresh'	=> 120,
		] );
	}
}
