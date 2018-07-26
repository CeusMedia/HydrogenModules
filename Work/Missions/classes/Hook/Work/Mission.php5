<?php
class Hook_Work_Mission /*extends CMF_Hydrogen_Hook*/{

	static public function onCollectNovelties( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$model		= new Model_Mission_Document( $env );
		$conditions	= array( 'modifiedAt' => '>'.( time() - 30 * 24 * 60 * 60 ) );
		$orders		= array( 'modifiedAt' => 'DESC' );
		foreach( $model->getAll( $conditions, $orders ) as $item ){
			$context->add( (object) array(
				'module'	=> 'Work_Missions',
				'type'		=> 'document',
				'typeLabel'	=> 'Dokument',
				'id'		=> $item->missionDocumentId,
				'title'		=> $item->filename,
				'timestamp'	=> max( $item->createdAt, $item->modifiedAt ),
				'url'		=> './work/mission/downloadDocument/'.$item->missionId.'/'.$item->missionDocumentId,
			) );
		}
	}

	static public function onRegisterTimerModule( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$context->registerModule( (object) array(
			'moduleId'		=> 'Work_Missions',
			'typeLabel'		=> 'Aufgabe',
			'modelClass'	=> 'Model_Mission',
			'linkDetails'	=> 'work/mission/view/{id}',
		) );
	}

	static public function onDatabaseLockReleaseCheck( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$controllerAction	= $data['controller'].'/'.$data['action'];
		$skipActions		= array(
			'work/mission/export/ical',
			'work/mission/addDocument',
			'work/mission/edit',
			'work/time/add',
			'work/time/start',
			'work/time/pause',
			'work/time/stop',
		);
		if( in_array( $controllerAction, $skipActions ) )
			return FALSE;
		if( !$data['userId'] )
			return FALSE;
		$logicLock	= new Logic_Database_Lock( $env );
		$locks		= $logicLock->getUserLocks( $data['userId'] );
		foreach( $locks as $lock ){
			if( $lock->subject === "Work_Missions" ){
//				error_log( time().": Missions:onDatabaseLockReleaseCheck: ".json_encode( $data['request']->get( '__path') )."\n", 3, "unlock.log" );
				$logicLock->unlock( $lock->subject, $lock->entryId, $data['userId'] );
			}
		}
	}

	static public function onProjectRemove( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$data				= (object) $data;
		$data->informOthers	= isset( $data->informOthers ) ? $data->informOthers : FALSE;
		if( empty( $data->projectId ) ){
			$message	= 'Hook "Work_Missions::onProjectRemove" is missing project ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$modelProject	= new Model_Project( $env );
		if( !( $project = $modelProject->get( $data->projectId ) ) ){
			$message	= 'Hook "Work_Missions::onProjectRemove": Invalid project ID.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$logicMission	= Logic_Work_Mission::getInstance( $env );
		$modelMission	= new Model_Mission( $env );
		$missions		= $modelMission->getAllByIndex( 'projectId', $data->projectId );
		foreach( $missions as $mission ){
			$logicMission->removeMission( $mission->missionId );
		}
	}

	static public function onListProjectRelations( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$modelProject	= new Model_Project( $env );
		if( empty( $data->projectId ) ){
			$message	= 'Hook "Work_Missions::onListProjectRelations" is missing project ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		if( !( $project = $modelProject->get( $data->projectId ) ) ){
			$message	= 'Hook "Work_Missions::onListProjectRelations": Invalid project ID.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$data->activeOnly	= isset( $data->activeOnly ) ? $data->activeOnly : FALSE;
		$data->linkable		= isset( $data->linkable ) ? $data->linkable : FALSE;

		$modelMission	= new Model_Mission( $env );
		$words			= $env->getLanguage()->getWords( 'work/mission' );
		$statusesActive	= array( 0, 1, 2, 3 );
		$list			= array();
		$indices		= array( 'projectId' => $data->projectId );
		if( $data->activeOnly )
			$indices['status']	= $statusesActive;
		$orders			= array( 'type' => 'DESC', 'title' => 'ASC' );
		$missions		= $modelMission->getAllByIndices( $indices, $orders );	//  ...

		$icons			= array(
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-thumb-tack' ) ),
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-clock-o' ) ),
		);
		foreach( $missions as $mission ){
			$icon		= $icons[$mission->type];
			$isOpen		= in_array( $mission->status, $statusesActive );
			$status		= '('.$words['states'][$mission->status].')';
			$status		= UI_HTML_Tag::create( 'small', $status, array( 'class' => 'muted' ) );
			$title		= $isOpen ? $mission->title : UI_HTML_Tag::create( 'del', $mission->title );
			$label		= $icon.'&nbsp;'.$title.'&nbsp;'.$status;
			$list[]		= (object) array(
				'id'		=> $data->linkable ? $mission->missionId : NULL,
				'label'		=> $label,
			);
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$data,																					//  hook content data
			$module,																				//  module called by hook
			'entity',																				//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Work_Mission',																			//  controller of entity
			'edit'																					//  action to view or edit entity
		);
	}

	static public function onListUserRelations( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		if( empty( $data->userId ) ){
			$message	= 'Hook "Work_Missions::___onListUserRelations" is missing user ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$logic			= Logic_Project::getInstance( $env );
		$modelProject	= new Model_Project( $env );
		$words			= $env->getLanguage()->getWords( 'work/mission' );

		$projectIds		= array();
		$projects		= $logic->getUserProjects( $data->userId, FALSE );
		foreach( $projects as $project ){
			$users		= $logic->getProjectUsers( $project->projectId );
			if( count( $users ) !== 1 || !isset( $users[$data->userId] ) )						//  other users in project
				continue;
			$projectIds[]	= $project->projectId;
		}

		$data->activeOnly	= isset( $data->activeOnly ) ? $data->activeOnly : FALSE;
		$data->linkable		= isset( $data->linkable ) ? $data->linkable : FALSE;
		$statusesActive	= array( 0, 1, 2, 3 );
		$list			= array();
		$modelMission	= new Model_Mission( $env );
		$indices		= array( 'projectId' => $projectIds );
		if( $data->activeOnly )
			$indices['status']	= $statusesActive;
		$orders			= array( 'type' => 'DESC', 'title' => 'ASC' );

		$missions		= $modelMission->getAllByIndices( $indices, $orders );	//  ...
		$icons			= array(
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-thumb-tack' ) ),
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-clock-o' ) ),
		);
		foreach( $missions as $mission ){
			$icon		= $icons[$mission->type];
			$isOpen		= in_array( $mission->status, $statusesActive );
			$status		= '('.$words['states'][$mission->status].')';
			$status		= UI_HTML_Tag::create( 'small', $status, array( 'class' => 'muted' ) );
			$title		= $isOpen ? $mission->title : UI_HTML_Tag::create( 'del', $mission->title );
			$label		= $icon.'&nbsp;'.$title.'&nbsp;'.$status;
			$list[]		= (object) array(
				'id'		=> $data->linkable ? $mission->missionId : NULL,
				'label'		=> $label,
			);
		}
		if( $list )
			View_Helper_ItemRelationLister::enqueueRelations(
				$data,																			//  hook content data
				$module,																		//  module called by hook
				'entity',																		//  relation type: entity or relation
				$list,																			//  list of related items
				$words['hook-relations']['label'],												//  label of type of related items
				'Work_Mission',																	//  controller of entity
				'edit'																			//  action to view or edit entity
			);
	}

	static public function onUserRemove( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$data				= (object) $data;
		$data->informOthers	= isset( $data->informOthers ) ? $data->informOthers : FALSE;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Work_Missions::___onUserRemove" is missing user ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$logicProject	= Logic_Project::getInstance( $env );
		$logicMission	= Logic_Work_Mission::getInstance( $env );
		$modelProject	= new Model_Project( $env );
		$modelMission	= new Model_Mission( $env );
		$modelFilter	= new Model_Mission_Filter( $env );
		$words			= $env->getLanguage()->getWords( 'work/mission' );
		$lists			= (object) array( 'entities' => array(), 'relations' => array() );

		$modelFilter->removeByIndex( 'userId', $data->userId );

		$nrMissionsRemoved	= 0;
		$nrMissionsChanged	= 0;
		$projectIds		= array();
		$projects		= $logicProject->getUserProjects( $data->userId, FALSE );
		foreach( $projects as $project ){
			$users		= $logicProject->getProjectUsers( $project->projectId );
			$missions	= $modelMission->getAllByIndex( 'projectId', $project->projectId );
			if( count( $users ) === 1 && isset( $users[$data->userId] ) ){						//  no other users in project
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
				if( $mission->creatorId == $data->userId )
					$mission->creatorId		= $nextUserId;
				if( $mission->workerId == $data->userId )
					$mission->workerId		= $mission->creatorId;
				if( $mission->modifierId == $data->userId )
					$mission->modifierId	= 0;
				if( $old != $mission ){
					$nrMissionsChanged++;
					$modelMission->edit( $mission->missionId, (array) $mission );
					if( $data->informOthers && in_array( $mission->status, array( 1, 2, 3, 4 ) ) )
						$logicMission->noteChange( 'update', $mission->missionId, $old, $data->userId );
				}
			}
		}
		if( $nrMissionsRemoved )
			$env->getMessenger()->noteSuccess( 'Removed %d missions.', $nrMissionsRemoved );
		if( $nrMissionsChanged )
			$env->getMessenger()->noteSuccess( 'Reassigned %d missions.', $nrMissionsChanged );
	}

	static public function onStartTimer( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$timer	= $data['timer'];
		if( $timer->module === 'Work_Missions' && $timer->moduleId ){
			$model		= new Model_Mission( $env );
			$mission	= $model->get( $timer->moduleId );
			if( in_array( $mission->status, array( -2, -1, 0, 1, 3, 4 ) ) ){
				$model->edit( $timer->moduleId, array( 'status' => 2 ) );
			}
		}
	}

	static public function onPauseTimer( CMF_Hydrogen_Environment $env, $context, $module, $data ){
//		self::___onStartTimer( $env, $context, $module, $data );
	}

	static public function onStopTimer( CMF_Hydrogen_Environment $env, $context, $module, $data ){
//		self::___onStartTimer( $env, $context, $module, $data );
	}

	static public function onRegisterDashboardPanels( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$context->registerPanel( 'work-mission-my-today', array(
			'url'		=> 'work/mission/ajaxRenderDashboardPanel',
			'title'		=> 'Heute & Termine',
			'heading'	=> 'Heute & Termine',
			'icon'		=> 'fa fa-fw fa-calendar-o',
			'rank'		=> 10,
			'refresh'	=> 60,
		) );
		$context->registerPanel( 'work-mission-my-tasks', array(
			'url'		=> 'work/mission/ajaxRenderDashboardPanel',
			'title'		=> 'Aufgaben: Meine - Heute',
			'heading'	=> 'Meine heutigen Aufgaben',
			'icon'		=> 'fa fa-fw fa-thumb-tack',
			'rank'		=> 20,
			'refresh'	=> 120,
		) );
	}
}