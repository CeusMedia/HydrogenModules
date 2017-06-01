<?php
/**
 *	Controller.
 *	@version		$Id$
 */
/**
 *	Controller.
 *	@version		$Id$
 *	@todo			implement
 *	@todo			code documentation
 */
class Controller_Work_Mission extends CMF_Hydrogen_Controller{

	public function help( $topic = NULL ){
		$this->addData( 'topic', (string) $topic );
	}

	protected $acl;
	protected $filterKeyPrefix	= 'filter.work.mission.';
	protected $isEditor;
	protected $isViewer;
	protected $hasFullAccess	= FALSE;
	protected $lock;
	protected $logic;
	protected $logicProject;
	protected $messenger;
	protected $model;
	protected $request;
	protected $session;
	protected $useIssues		= FALSE;
	protected $useProjects		= TRUE;																//  @deprecated since projects module is required
	protected $userMap			= array();
	protected $userId;
	protected $userRoleId;
	protected $moduleConfig;
	protected $contentFormat;

	protected $defaultFilterValues	= array(
		'mode'		=> 'now',
		'states'	=> array(
			Model_Mission::STATUS_NEW,
			Model_Mission::STATUS_ACCEPTED,
			Model_Mission::STATUS_PROGRESS,
			Model_Mission::STATUS_READY
		),
		'priorities'	=> array(
			Model_Mission::PRIORITY_NONE,
			Model_Mission::PRIORITY_HIGHEST,
			Model_Mission::PRIORITY_HIGH,
			Model_Mission::PRIORITY_NORMAL,
			Model_Mission::PRIORITY_LOW,
			Model_Mission::PRIORITY_LOWEST
		),
		'types'			=> array(
			Model_Mission::TYPE_TASK,
			Model_Mission::TYPE_EVENT
		),
		'order'			=> 'priority',
		'direction'		=> 'ASC',
	);

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->acl			= $this->env->getAcl();

		$this->model		= new Model_Mission( $this->env );
		$this->logic		= Logic_Work_Mission::getInstance( $this->env );
		$this->logicAuth	= Logic_Authentication::getInstance( $this->env );

		$this->isEditor		= $this->acl->has( 'work/mission', 'edit' );
		$this->isViewer		= $this->acl->has( 'work/mission', 'view' );
		$this->useProjects	= TRUE;//$this->env->getModules()->has( 'Manage_Projects' );
		$this->useIssues	= $this->env->getModules()->has( 'Work_Issues' );
		$this->useTimer		= $this->env->getModules()->has( 'Work_Timer' );

		$this->userId		= $this->logicAuth->getCurrentUserId();
		$this->userRoleId	= $this->logicAuth->getCurrentRoleId();

		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.work_missions.', TRUE );
		$this->contentFormat	= $this->moduleConfig->get( 'format' );

//		if( !$this->userId || !$this->isViewer )
//			$this->restart( NULL, FALSE, 401 );

		$this->logicProject	= new Logic_Project( $this->env );
		$this->userMap		= $this->logicProject->getCoworkers( $this->userId, NULL, TRUE );

		//  @todo	kriss: DO NOT DO THIS!!! (badly scaling)
//		$model			= new Model_User( $this->env );
//		foreach( $model->getAll() as $user )
//			$this->userMap[$user->userId]	= $user;

		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'useProjects', $this->useProjects );										//  @todo remove deprecated 'useProjects'
		$this->addData( 'useTimer', $this->useTimer );
		$this->addData( 'useIssues', $this->useIssues );
		$this->addData( 'acl', $this->acl );
		$this->addData( 'userId', $this->userRoleId );
		$this->addData( 'userRoleId', $this->userRoleId );

		$this->userProjects		= $this->logic->getUserProjects( $this->userId, TRUE );
		if( $this->hasFullAccess() )
			$this->userProjects		= $this->logic->getUserProjects( $this->userId );
		$this->addData( 'projects', $this->userProjects );
		if( $this->env->getModules()->has( 'Resource_Database_Lock' ) )
			$this->lock	= new Logic_Database_Lock( $this->env );

//		$this->env->getModules()->callHook( 'Test', 'test', array() );
	}

	static public function ___onCollectNovelties( $env, $context, $module, $data = array() ){
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

	static public function ___onRegisterTimerModule( $env, $context, $module, $data = array() ){
		$context->registerModule( (object) array(
			'moduleId'		=> 'Work_Missions',
			'typeLabel'		=> 'Aufgabe',
			'modelClass'	=> 'Model_Mission',
			'linkDetails'	=> 'work/mission/view/{id}',
		) );
	}

	static public function ___onDatabaseLockReleaseCheck( $env, $context, $module, $data = array() ){
		$controllerAction	= $data['controller'].'/'.$data['action'];
		$skipActions		= array(
			'work/mission/edit',
			'work/mission/export/ical',
		);
		if( !preg_match( "@^work/mission@", $data['controller'] ) )
			return FALSE;
		if( in_array( $controllerAction, $skipActions ) )
			return FALSE;
		return Logic_Database_Lock::release( $env, 'Work_Missions' );
	}

	static public function ___onProjectRemove( $env, $context, $module, $data ){
		$projectId	= $data['projectId'];
		foreach( $this->model->getAllByIndex( 'projectId', $projectId ) as $mission ){
			$this->logic->removeMission( $mission->missionId );
		}
	}

	static public function ___onListProjectRelations( $env, $context, $module, $data ){
		$modelProject	= new Model_Project( $env );
		if( empty( $data->projectId ) ){
			$message	= 'Hook "Work_Missions::___onListProjectRelations" is missing project ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		if( !( $project = $modelProject->get( $data->projectId ) ) ){
			$message	= 'Hook "Work_Missions::___onListProjectRelations": Invalid project ID.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$data->activeOnly	= isset( $data->activeOnly ) ? $data->activeOnly : FALSE;
		$data->linkable		= isset( $data->linkable ) ? $data->linkable : FALSE;
		$language		= $env->getLanguage();
		$statusesActive	= array( 0, 1, 2, 3 );
		$list			= array();
		$modelMission	= new Model_Mission( $env );
		$indices		= array( 'projectId' => $data->projectId );
		if( $data->activeOnly )
			$indices['status']	= $statusesActive;
		$orders			= array( 'type' => 'DESC', 'title' => 'ASC' );
		$missions		= $modelMission->getAllByIndices( $indices, $orders );	//  ...

		$icons			= array(
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-thumb-tack' ) ),
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-clock-o' ) ),
		);
		$words		= $language->getWords( 'work/mission' );
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

	static public function ___onStartTimer( $env, $context, $module, $data ){
		$timer	= $data['timer'];
		if( $timer->module === 'Work_Missions' && $timer->moduleId ){
			$model		= new Model_Mission( $env );
			$mission	= $model->get( $timer->moduleId );
			if( in_array( $mission->status, array( -2, -1, 0, 1, 3, 4 ) ) ){
				$model->edit( $timer->moduleId, array( 'status' => 2 ) );
			}
		}
	}

	static public function ___onPauseTimer( $env, $context, $module, $data ){
//		self::___onStartTimer( $env, $context, $module, $data );
	}

	static public function ___onStopTimer( $env, $context, $module, $data ){
//		self::___onStartTimer( $env, $context, $module, $data );
	}

	static public function ___onRegisterDashboardPanels( $env, $context, $module, $data ){
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

	/**
	 *	Add a new mission.
	 *	Redirects to index if editor right is missing.
	 *	@access		public
	 *	@param		integer		$copyFromMissionId		ID of mission to copy default values from (optional)
	 *	@return		void
	 */
	public function add( $copyFromMissionId = NULL ){
		$words			= (object) $this->getWords( 'add' );

		if( !$this->isEditor ){
			$this->messenger->noteError( $words->msgNotEditor );
			$this->restart( NULL, TRUE, 403 );
		}
		if( $this->useProjects && !$this->userProjects ){											//  @todo remove deprecated 'useProjects'
			$this->messenger->noteNotice( $words->msgNoProjectYet );
			$this->restart( './manage/project/add?from=work/mission/add' );
		}

		if( $copyFromMissionId && $mission = $this->model->get( $copyFromMissionId ) ){
			foreach( $mission as $key => $value )
				if( !in_array( $key, array( 'dayStart', 'dayEnd', 'status', 'createdAt', 'modifiedAt' ) ) )
					$this->request->set( $key, $value );
			$this->request->set( 'dayStart', date( 'Y-m-d' ) );
		}

		$title		= $this->request->get( 'title' );
		$status		= $this->request->get( 'status' );
		$dayStart	= !$this->request->get( 'type' ) ? $this->request->get( 'dayWork' ) : $this->request->get( 'dayStart' );
		$dayEnd		= !$this->request->get( 'type' ) ? $this->request->get( 'dayDue' ) : $this->request->get( 'dayEnd' );
		$format		= $this->request->get( 'format' ) ? $this->request->get( 'format' ) : $this->contentFormat;

		if( $this->request->has( 'add' ) ){
			if( !$title )
				$this->messenger->noteError( $words->msgNoTitle );
			if( !$this->messenger->gotError() ){
				$type		= (int) $this->request->get( 'type' );
				$data	= array(
					'creatorId'			=> (int) $this->userId,
					'modifierId'		=> (int) $this->userId,
					'workerId'			=> (int) $this->request->get( 'workerId' ),
					'projectId'			=> (int) $this->request->get( 'projectId' ),
					'type'				=> $type,
					'priority'			=> (int) $this->request->get( 'priority' ),
					'status'			=> $status,
					'title'				=> $title,
					'content'			=> $this->request->get( 'content' ),
					'dayStart'			=> $this->logic->getDate( $dayStart ),
					'dayEnd'			=> $this->logic->getDate( $dayEnd ),
					'timeStart'			=> $this->request->get( 'timeStart' ),
					'timeEnd'			=> $this->request->get( 'timeEnd' ),
//					'minutesProjected'	=> $this->getMinutesFromInput( $this->request->get( 'minutesProjected' ) ),
					'minutesProjected'	=> round( View_Work_Mission::parseTime( $this->request->get( 'timeProjected' ) ) / 60 ),
					'location'			=> $this->request->get( 'location' ),
					'reference'			=> $this->request->get( 'reference' ),
					'format'			=> $format,
					'createdAt'			=> time(),
				);
				$missionId	= $this->model->add( $data, FALSE );
				$message	= $type == 1 ? $words->msgSuccessEvent : $words->msgSuccessTask;
				$this->messenger->noteSuccess( $message );
				$this->logic->noteChange( 'new', $missionId, NULL, $this->userId );
				$this->restart( 'view/'.$missionId, TRUE );
			}
		}
		$mission	= array();
		foreach( $this->model->getColumns() as $key )
			$mission[$key]	= strlen( $this->request->get( $key ) ) ? $this->request->get( $key ) : NULL;
		if( $mission['priority'] === NULL )
			$mission['priority']	= 3;
		if( $mission['status'] === NULL )
			$mission['status']	= 0;
		if( $mission['projectId'] === NULL )
			$mission['projectId']	= $this->logicProject->getDefaultProject( $this->userId );

		//  --  set current date for all date fields  --  //
		if( !$mission['dayStart'] )
			$mission['dayStart']	= date( 'Y-m-d' );
		if( !$mission['dayEnd'] )
			$mission['dayEnd']		= date( 'Y-m-d' );
		if( !$mission['format'] )
			$mission['format']		= $this->contentFormat;

//		$mission['minutesProjected']	= $this->getMinutesFromInput( $this->request->get( 'minutesProjected' ) );
		$this->addData( 'mission', (object) $mission );
		$this->addData( 'users', $this->userMap );
		$this->addData( 'userId', $this->userId );
		$this->addData( 'day', (int) $this->session->get( $this->filterKeyPrefix.'day' ) );
		$this->addData( 'format', $format );

		if( $this->useProjects ){																	//  @todo remove deprecated 'useProjects'
			$this->addData( 'userProjects', $this->userProjects );
		}
	}

	public function addDocument( $missionId ){
		$upload		= (object) $this->env->getRequest()->get( 'document' );
		$model		= new Model_Mission_Document( $this->env );

		$path		= 'contents/documents/missions/';
		if( !file_exists( $path ) )
			mkdir( $path, 0777, TRUE );
		$document	= $model->getByIndices( array(
			'missionId'	=> $missionId,
			'filename'	=> $upload->name,
		) );
		$hashname	= $document ? $document->hashname : Alg_ID::uuid();
		$logic		= new Logic_Upload( $this->env );
//		$logic->checkMimeType( array() );
//		$logic->checkSize();
		$logic->setUpload( $upload );
		$logic->saveTo( $path.$hashname );

		if( $document ){
			$model->edit( $document->missionDocumentId, array(
				'userId'		=> $this->userId,
				'size'			=> $upload->size,
				'modifiedAt'	=> time(),
			) );
		}
		else{
			$model->add( array(
				'missionId'		=> $missionId,
				'userId'		=> $this->userId,
				'size'			=> $upload->size,
				'mimeType'		=> $upload->type,
				'filename'		=> $upload->name,
				'hashname'		=> $hashname,
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			) );
		}

//		$from	= $this->env->getRequest()->has( 'from' ) ? $this->env->getRequest()->has( 'from' );
		$this->restart( 'edit/'.$missionId, TRUE );
	}

	public function ajaxGetProjectUsers( $projectId ){
		$list	= array();
		if( $this->useProjects ){																	//  @todo remove deprecated 'useProjects'
			$model	= new Model_Project( $this->env );
			$users	= $model->getProjectUsers( (int) $projectId );
			if( array_key_exists( $this->userId, $users ) || $this->hasFullAccess() ){
				foreach( $users as $user )
					$list[$user->username]    = $user;
			}
		}
		ksort( $list );
		print( json_encode( array_values( $list ) ) );
		exit;
	}

	public function ajaxRenderContent(){
		$content	= $this->env->getRequest()->get( 'content' );
		$html		= View_Helper_Markdown::transformStatic( $this->env, $content );
		header( "Content-length: ".strlen( $html ) );
		header( "Content-type: text/html" );
		print( $html );
		exit;
	}

	public function ajaxRenderDashboardPanel( $panelId ){
		$this->addData( 'panelId', $panelId );
		$logic		= Logic_Work_Mission::getInstance( $this->env );
		switch( $panelId ){
			case 'work-mission-my-tasks':
				$conditions		= array(
					'status'	=> array( 0, 1, 2, 3 ),
					'type'		=> 0,
					'dayStart'	=> '<='.date( 'Y-m-d', time() ),
//					'dayEnd'	=> '>='.date( 'Y-m-d', time() ),
					'workerId'	=> $this->userId,
				);
				$orders		= array(
					'priority'	=> 'ASC',
					'title'		=> 'ASC',
				);
				$missions	= $logic->getUserMissions( $this->userId, $conditions, $orders );
				$this->addData( 'tasks', $missions );
				break;
			case 'work-mission-my-today':
			default:
				$conditions	= array(
					'type'			=> 1,
					'status'		=> array( 0, 1, 2, 3 ),
					'dayStart'		=> date( 'Y-m-d' ),
				);
				$orders	= array( 'timeStart' => 'ASC' );
				$events	= $logic->getUserMissions( $this->userId, $conditions, $orders );
				$this->addData( 'events', $events );
				break;
		}
		return $this->view->ajaxRenderDashboardPanel();
	}

	public function ajaxRenderIndex(){
		$mode	= $this->session->get( 'filter.work.mission.mode' );
		if( $mode && $mode !== 'now' )
			$this->redirect( 'work/mission/'.$mode, 'ajaxRenderIndex', func_get_args() );
		else{
			$words		= $this->getWords();

			$day		= (int) $this->session->get( 'filter.work.mission.day' );

			$missions	= $this->getFilteredMissions( $this->userId );
			$missions	= array_slice( $missions, 0, 100 );										//  @todo	kriss: make configurable

			$listLarge		= new View_Helper_Work_Mission_List_Days( $this->env );
			$listLarge->setMissions( $missions );
			$listLarge->setWords( $words );

			$listSmall		= new View_Helper_Work_Mission_List_DaysSmall( $this->env );
			$listSmall->setMissions( $listLarge->getDayMissions( $day ) );
			$listSmall->setWords( $words );

			$allDayMissions	= $listLarge->getDayMissions();

			$buttonsLarge	= new View_Helper_Work_Mission_List_DayControls( $this->env );
			$buttonsLarge->setWords( $words );
			$buttonsLarge->setDayMissions( $allDayMissions );

			$buttonsSmall	= new View_Helper_Work_Mission_List_DayControlsSmall( $this->env );
			$buttonsSmall->setWords( $words );
			$buttonsSmall->setDayMissions( $allDayMissions );

			$total	= 0;
			foreach( $allDayMissions as $entry )
				$total += count( $entry );

			$data		= array(
				'day'		=> $day,
				'items'		=> $allDayMissions[$day],//$listLarge->getDayMissions( $day ),
				'count'		=> count( $allDayMissions[$day] ),//$listLarge->getDayMissions( $day ) ),
				'total'		=> $total,
				'buttons'	=> array(
					'large'	=> $buttonsLarge->render(),
					'small'	=> $buttonsSmall->render(),
				),
				'lists'		=> array(
					'large'	=> $listLarge->renderDayList( 1, $day, TRUE, TRUE, FALSE, TRUE ),
					'small'	=> $listSmall->renderDayList( 1, $day, TRUE, TRUE, FALSE, !TRUE )
				)
			);
			print( json_encode( $data ) );
			exit;
		}
	}

	public function ajaxRenderMissionContent( $missionId, $version = NULL, $versionCompare = NULL ){
		try{
//			if( !$this->request->isAjax() )
//				throw new RuntimeException( "No denied" );
			if( !( $mission	= $this->model->get( $missionId ) ) )
				throw new InvalidArgumentException( 'Invalid mission id' );
//			if( !projectMember )
//				throw new InvalidArgumentException( 'No access to this mission' );
			$content	= View_Helper_Markdown::transformStatic( $this->env, $mission->content );
			if( ( $version = (int) $version ) !== 0 ){
				if( $version > 0 ){
					if( !( $data = $this->logic->getVersion( $missionId, $version ) ) )
						throw new InvalidArgumentException( 'Invalid version to show' );
					$content	= View_Helper_Markdown::transformStatic( $this->env, $data->content );
				}
				if( ( $versionCompare = (int) $versionCompare ) > 0 ){
					if( $version != $versionCompare ){
						if( !( $data = $this->logic->getVersion( $missionId, $versionCompare ) ) )
							throw new InvalidArgumentException( 'Invalid version to compare to' );
						$compareWith = View_Helper_Markdown::transformStatic( $this->env, $data->content );
						$content	= View_Helper_HtmlDiff::renderStatic( $this->env, $compareWith, $content );
					}
				}
			}
			$this->handleJsonResponse( "data", (string) $content );
		}
		catch( Exception $e ){
			$this->handleJsonResponse( "error", $e->getMessage() );
		}
	}

	public function ajaxSaveContent( $missionId ){
		$content	= $this->env->getRequest()->get( 'content' );
		$this->model->edit( $missionId, array(														//  store in database
			'content'		=> $content,															//  - new content
			'modifierId'	=> $this->userId,														//  - modifying user id
			'modifiedAt'	=> time(),																//  - modification time
		), FALSE );																					//  without striping tags
		$html		= View_Helper_Markdown::transformStatic( $this->env, $content );
		header( 'Content-length: '.strlen( $html ) );
		header( 'Content-type: text/html' );
		print $html;
		exit;
	}

	public function ajaxSelectDay( $day ){
		$this->session->set( $this->filterKeyPrefix.'day', (int) $day );
		$this->ajaxRenderIndex();
	}

	protected function assignFilters(){
		$this->addData( 'userId', $this->userId );
		$this->addData( 'viewType', (int) $this->session->get( 'work-mission-view-type' ) );

		$direction	= $this->session->get( $this->filterKeyPrefix.'direction' );
		$order		= $this->session->get( $this->filterKeyPrefix.'order' );

		if( !$order )
			$this->restart( './work/mission/filter?order=priority' );

		$direction	= $direction ? $direction : 'ASC';
		$this->session->set( $this->filterKeyPrefix.'direction', $direction );

		$this->setData( array(																		//  assign data t$
			'userProjects'	=> $this->userProjects,													//  add user projec$
			'users'			=> $this->userMap,														//  add user map
		) );

		$this->addData( 'filterTypes', $this->session->get( $this->filterKeyPrefix.'types' ) );
		$this->addData( 'filterPriorities', $this->session->get( $this->filterKeyPrefix.'priorities' ) );
		$this->addData( 'filterStates', $this->session->get( $this->filterKeyPrefix.'states' ) );
		$this->addData( 'filterOrder', $order );
		$this->addData( 'filterProjects', $this->session->get( $this->filterKeyPrefix.'projects' ) );
		$this->addData( 'filterDirection', $direction );
		$this->addData( 'filterMode', $this->session->get( 'filter.work.mission.mode' ) );
		$this->addData( 'filterQuery', $this->session->get( $this->filterKeyPrefix.'query' ) );
		$this->addData( 'filterWorkers', $this->session->get( $this->filterKeyPrefix.'workers' ) );
		$this->addData( 'defaultFilterValues', $this->defaultFilterValues );
//		$this->addData( 'coworkers', $this->userMap )
		$this->addData( 'wordsFilter', $this->env->getLanguage()->getWords( 'work/mission' ) );
	}

	public function bulk(){
		$action	= $this->request->get( 'action' );
		$missionIds	= $this->request->get( 'missionIds' );

	print_m( $action );
	print_m( $missionIds );
	die;
	}

	/**
 	 *	Moves a mission by several days or to a given date.
	 *	Receives date or day difference using POST.
	 *	A day difference can be formatted like +2 or -2.
	 *	Moving a task mission will only affect start date but end date will remain unchanged.
	 *	Moving an event mission will affect start and end date.
	 *	If called using AJAX list rendering is triggered.
	 *	@access		public
	 *	@param		integer		$mission		ID of mission to move in time
	 *	@return		void
	 *	@todo		kriss: enable this feature for AJAX called EXCEPT gid list
	 */
	public function changeDay( $missionId ){
		$date		= trim( $this->request->get( 'date' ) );
		$mission	= $this->model->get( $missionId );
		$data		= array(
			'modifierId'	=> $this->userId,
			'modifiedAt'	=> time(),
		);
		$change		= "";

		if( preg_match( "/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]+$/", $date ) ){
			$date	= strtotime( $date );
			$diff	= ( $date - strtotime( $mission->dayStart ) ) / ( 24 * 3600 );
			$sign	= $diff >= 0 ? "+" : "-";
			$number	= round( abs( $diff ) );
			$change	= $sign." ".$number."day";
		}
		else if( preg_match( "/^[+-][0-9]+$/", $date ) ){
			$sign	= substr( $date, 0, 1 );					//  extract direction to move
			$number	= substr( $date, 1 );						//  extract number of days to move
			$change	= $sign." ".$number."day";
		}
		if( $change ){
			$date	= new  DateTime( $mission->dayStart );
			$data['dayStart'] = $date->modify( $change )->format( "Y-m-d" );
			if( $mission->dayEnd ){													//  mission has a duration
				if( $mission->type == 1 ){											//  mission is an event, not a task
					$date	= new  DateTime( $mission->dayEnd );					//  take end timestamp and ...
					$data['dayEnd'] = $date->modify( $change )->format( "Y-m-d" );  //  ... store new moved end date
				}
			}
			$this->model->edit( $missionId, $data );
			$this->logic->noteChange( 'update', $missionId, $mission, $this->userId );
		}
		if( $this->env->request->isAjax() )
			$this->ajaxRenderIndex();
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@todo			check if this method is needed anymore
	 */
	public function checkForUpdate( $userId ){
		if( file_exists( "update-".$userId ) ){
			@unlink( "update-".$userId );
			print json_encode( TRUE );
		}
		else{
			print json_encode( FALSE );
		}
		exit;
	}

	protected function checkIsEditor( $missionId = NULL, $strict = TRUE, $status = 403 ){
		if( $this->isEditor )
			return TRUE;
		if( !$strict )
			return FALSE;
		$words		= (object) $this->getWords( 'msg' );
		$message	= $words->errorNoRightToAdd;
		$redirect	= NULL;
		if( $missionId ){
			$message	= $words->errorNoRightToEdit;
			$redirect	= 'view/'.$missionId;
		}
		$this->env->getMessenger()->noteError( $message );
		$this->restart( $redirect, TRUE, $status );
	}


	public function close( $missionId ){
		$this->checkIsEditor( $missionId );
		$words		= (object) $this->getWords( 'edit' );
		$mission	= $this->model->get( $missionId );
		$minutes	= ceil( View_Work_Mission::parseTime( $this->request->get( 'timeRequired' ) ) / 60 );
		$this->model->edit( $missionId, array(														//  store in database
			'status'			=> $this->request->get( 'status' ),									//  - new status
//			'hoursRequired'		=> $this->request->get( 'hoursRequired' ),							//  - number of required hours
			'minutesRequired'	=> $minutes,														//  - number of required minutes
			'modifierId'		=> $this->userId,													//  - modifying user id
			'modifiedAt'		=> time(),															//  - modification time
		) );
		$this->logic->noteChange( 'update', $missionId, $mission, $this->userId );
		$this->messenger->noteSuccess( $words->msgSuccessClosed );
		$this->restart( NULL, TRUE );
	}

	public function convertToIssue( $missionId ){
		die( "Not implemented yet" );
	}

	public function convertContent( $missionId, $formatIn, $formatOut ){
		$this->checkIsEditor( $missionId );
		$words			= (object) $this->getWords( 'edit' );
		$mission		= $this->model->get( $missionId );
		if( !$mission )
			$this->messenger->noteError( $words->msgInvalidId );
		if( strtoupper( $formatIn ) === "MARKDOWN" && strtoupper( $formatOut ) === "HTML" ){
			$content	= View_Helper_Markdown::transformStatic( $this->env, $mission->content );
			$data	= array(
				'content'		=> $content,
				'format'		=> 'HTML',
				'modifiedAt'	=> time(),
				'modifierId'	=> $this->userId,
			);
			$this->model->edit( $missionId, $data, FALSE );
		}
		$this->restart( 'edit/'.$missionId, TRUE );
	}

	/**
	 *	@todo  			check sanity, see below
	 */
	protected function deliverDocument( $missionId, $missionDocumentId, $download = FALSE ){
	//	check missionId against user
	//	check missionDocumentId against missionId

		$model		= new Model_Mission_Document( $this->env );
		$document	= $model->get( $missionDocumentId );
		if( !$document ){
			$this->messenger->noteError( 'Document ID is invalid' );
			$this->restart( './view/'.$missionId );
		}
		$pathname	= "contents/documents/missions/".$document->hashname;
		if( !file_exists( $pathname ) ){
			$this->messenger->noteError( 'Document is not existing' );
			$this->restart( './view/'.$missionId );
		}
		$timestamp	= max( $document->createdAt, $document->modifiedAt );
		$disposition	= $download ? 'attachment' : 'inline';
		header( 'Content-Type: '.$document->mimeType );
		header( 'Content-Length: '.$document->size );
		header( 'Last-Modified: '.date( 'r', $timestamp ) );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-Disposition: '.$disposition.'; filename='.$document->filename );

		$fp = @fopen( $pathname, "rb" );
		if( !$fp )
			header("HTTP/1.0 500 Internal Server Error");
		fpassthru( $fp );
		exit;
	}

	public function downloadDocument( $missionId, $missionDocumentId ){
		$this->deliverDocument( $missionId, $missionDocumentId, TRUE );
	}

	public function edit( $missionId ){
		$this->checkIsEditor( $missionId );
		$words			= (object) $this->getWords( 'edit' );
		$mission		= $this->model->get( $missionId );
		if( !$mission )
			$this->messenger->noteError( $words->msgInvalidId );
		if( !in_array( $mission->status, array( -1, 0, 1, 2, 3 ) ) ){
			$this->messenger->noteError( $words->msgArchived );
			$this->restart( 'view/'.$missionId, TRUE );
		}
		if( $mission->status < 0 || $mission->status > 3 )
			$this->session->set( 'filter.work.mission.mode', 'archive' );
		else if( $this->session->get( 'filter.work.mission.mode' ) == 'archive' )
			$this->session->set( 'filter.work.mission.mode', 'now' );

		if( $this->useProjects ){																	//  @todo remove deprecated 'useProjects'
			if( !array_key_exists( $mission->projectId, $this->userProjects ) )
				$this->messenger->noteError( $words->msgInvalidProject );
		}
		if( $this->messenger->gotError() )
			$this->restart( NULL, TRUE );

		if( $this->lock ){
			if( $this->lock->isLockedByOther( 'Work_Missions', $missionId ) ){
				$lockUser	= $this->lock->getLockUser( 'Work_Missions', $missionId );
				$this->messenger->noteNotice( $words->msgLocked, $lockUser->username );
				$this->restart( 'view/'.$missionId, TRUE );
			}
			else if( !$this->lock->isLockedByMe( 'Work_Missions', $missionId ) )
				$this->lock->lockByMe( 'Work_Missions', $missionId );
		}

		$title		= $this->request->get( 'title' );
		$dayStart	= $this->request->get( 'dayStart' );
		$dayEnd		= $this->request->get( 'dayEnd' );
		if( $this->request->get( 'type' ) == 0 ){
			$dayStart	= $this->logic->getDate( $this->request->get( 'dayWork' ) );
			$dayEnd		= $this->request->get( 'dayDue' ) ? $this->logic->getDate( $this->request->get( 'dayDue' ) ) : NULL;
		}
		$format		= $this->request->get( 'format' ) ? $this->request->get( 'format' ) : $this->contentFormat;

		if( $this->request->get( 'edit' ) ){
			if( !$title )
				$this->messenger->noteError( $words->msgNoTitle );
			if( !$this->messenger->gotError() ){
				$data	= array(
					'workerId'			=> (int) $this->request->get( 'workerId' ),
					'projectId'			=> (int) $this->request->get( 'projectId' ),
					'type'				=> (int) $this->request->get( 'type' ),
					'priority'			=> (int) $this->request->get( 'priority' ),
					'status'			=> (int) $this->request->get( 'status' ),
					'title'				=> $title,
					'dayStart'			=> $dayStart,
					'dayEnd'			=> $dayEnd,
					'timeStart'			=> $this->request->get( 'timeStart' ),
					'timeEnd'			=> $this->request->get( 'timeEnd' ),
//					'minutesProjected'	=> $this->getMinutesFromInput( $this->request->get( 'minutesProjected' ) ),
					'minutesProjected'	=> round( View_Work_Mission::parseTime( $this->request->get( 'timeProjected' ) ) / 60 ),
//					'minutesRequired'	=> $this->getMinutesFromInput( $this->request->get( 'minutesRequired' ) ),
//					'hoursProjected'	=> $this->request->get( 'hoursProjected' ) ? $this->request->get( 'hoursProjected' ) : NULL,
//					'hoursRequired'		=> $this->request->get( 'hoursRequired' ) ? $this->request->get( 'hoursRequired' ) : NULL,
					'location'			=> $this->request->get( 'location' ),
					'reference'			=> $this->request->get( 'reference' ),
					'format'			=> $format,
					'modifiedAt'		=> time(),
					'modifierId'		=> $this->userId,
				);
				if( /*strtoupper( $format ) == "HTML" || */$this->request->has( 'content' ) )
					$data['content']	= $this->request->get( 'content' );

				$this->model->edit( $missionId, $data, FALSE );
				$this->messenger->noteSuccess( $words->msgSuccess );
				if( $this->request->get( 'inform') )
					$this->logic->noteChange( 'update', $missionId, $mission, $this->userId );
				$this->logic->noteVersion( $missionId, $this->userId, $mission->content );
				$this->restart( './work/mission' );
			}
		}
		$modelUser	= new Model_User( $this->env );
		$mission->creator	= array_key_exists( $mission->creatorId, $this->userMap ) ? $this->userMap[$mission->creatorId] : NULL;
		$mission->modifier	= array_key_exists( $mission->modifierId, $this->userMap ) ? $this->userMap[$mission->modifierId] : NULL;
		$mission->worker	= array_key_exists( $mission->workerId, $this->userMap ) ? $this->userMap[$mission->workerId] : NULL;

		$this->addData( 'mission', $mission );
		$this->addData( 'users', $this->logicProject->getProjectUsers( $mission->projectId ) );
		$missionUsers		= array( $mission->creatorId => $mission->creator );
		if( $mission->workerId )
			$missionUsers[$mission->workerId]	= $mission->worker;

		if( $this->useProjects ){																	//  @todo remove deprecated 'useProjects'
			$model		= new Model_Project( $this->env );
			foreach( $model->getProjectUsers( (int) $mission->projectId ) as $user )
				$missionUsers[$user->userId]	= $user;
			$this->addData( 'userProjects', $this->userProjects );
		}
		$this->addData( 'missionUsers', $missionUsers );
		$this->addData( 'format', $mission->format ? $mission->format : $this->contentFormat );

		if( $this->useIssues ){
			$this->env->getLanguage()->load( 'work/issue' );
			$this->addData( 'wordsIssue', $this->env->getLanguage()->getWords( 'work/issue' ) );
		}

		if( $this->useTimer ){
			$logic	= Logic_Work_Timer::getInstance( $this->env );
			$conditions	= array(
				'module'	=> 'Work_Missions',
				'moduleId'	=> $mission->missionId,
				'status'	=> array( 0, 1, 2 ),
			);
			$this->addData( 'openTimers', $logic->countTimers( $conditions ) );

			$conditions	= array(
				'moduleId'	=> 0,
				'userId'	=> $this->userId,
			);
			$this->addData( 'unrelatedTimers', $logic->index( $conditions, array( 'title' => 'ASC' ) ) );
		}

		$model		= new Model_Mission_Document( $this->env );
		$orders		= array( 'modifiedAt' => 'DESC', 'createdAt' => 'DESC' );
		$documents	= $model->getAllByIndex( 'missionId', $missionId, $orders );
		$this->addData( 'documents', $documents );
	}

	public function filter( $reset = NULL){
		$sessionPrefix	= $this->getModeFilterKeyPrefix();
		if( $this->request->has( 'reset' ) || $reset ){
			$this->session->remove( $sessionPrefix.'query' );
			$this->session->remove( $sessionPrefix.'types' );
			$this->session->remove( $sessionPrefix.'priorities' );
			$this->session->remove( $sessionPrefix.'states' );
			$this->session->remove( $sessionPrefix.'projects' );
			$this->session->remove( $sessionPrefix.'order' );
			$this->session->remove( $sessionPrefix.'direction' );
			$this->session->remove( $sessionPrefix.'day' );
			$this->session->remove( $sessionPrefix.'workers' );
		}
		if( $this->request->has( 'access' ) )
			$this->session->set( $sessionPrefix.'access', $this->request->get( 'access' ) );
		if( $this->request->has( 'query' ) )
			$this->session->set( $sessionPrefix.'query', $this->request->get( 'query' ) );
		if( $this->request->has( 'types' ) )
			$this->session->set( $sessionPrefix.'types', $this->request->get( 'types' ) );
		if( $this->request->has( 'priorities' ) )
			$this->session->set( $sessionPrefix.'priorities', $this->request->get( 'priorities' ) );
		if( $this->request->has( 'states' ) )
			$this->session->set( $sessionPrefix.'states', $this->request->get( 'states' ) );
		if( $this->request->has( 'projects' ) )
			$this->session->set( $sessionPrefix.'projects', $this->request->get( 'projects' ) );
		if( $this->request->has( 'workers' ) )
			$this->session->set( $sessionPrefix.'workers', $this->request->get( 'workers' ) );
		if( $this->request->has( 'order' ) )
			$this->session->set( $sessionPrefix.'order', $this->request->get( 'order' ) );
		if( $this->request->has( 'direction' ) )
			$this->session->set( $sessionPrefix.'direction', $this->request->get( 'direction' ) );
#			if( $this->request->has( 'direction' ) )
#				$this->session->set( $sessionPrefix.'direction', $this->request->get( 'direction' ) );
		if( $this->request->isAjax() ){
			print( json_encode( (object) array(
				'session'	=> $this->session->getAll(),
				'request'	=> $this->request->getAll()
			) ) );
			exit;
		}
		$this->restart( '', TRUE );
//		$this->request->isAjax() ? exit : $this->restart( '', TRUE );
	}

	protected function getFilteredMissions( $userId, $additionalConditions = array(), $limit = 0, $offset = 0 ){
		$conditions	= $this->logic->getFilterConditions( $this->filterKeyPrefix, $additionalConditions );
		$direction	= $this->session->get( $this->filterKeyPrefix.'direction' );
		$order		= $this->session->get( $this->filterKeyPrefix.'order' );
		$orders		= array(					//  collect order pairs
			$order		=> $direction,			//  selected or default order and direction
			'timeStart'	=> 'ASC',				//  order events by start time
		);
		if( $order != "title" )					//  if not ordered by title
			$orders['title']	= 'ASC';		//  order by title at last
		$limits	= array();
		if( $limit !== NULL && (int) $limit >= 10 ){
			$limits	= array( abs( $offset ), $limit );
		}
		return $this->logic->getUserMissions( $userId, $conditions, $orders, $limits );
	}

	protected function getMinutesFromInput( $input ){
		if( !strlen( trim( $input ) ) )
			return 0;
		if( substr_count( $input, ":" ) ){
			$parts	= explode( ":", $input );
			return $parts[1] + $parts[0] * 60;
		}
		return (int) $input;
	}

	protected function getModeFilterKeyPrefix(){
		$mode	= '';
		if( $this->session->get( 'filter.work.mission.mode' ) !== 'now' )
			$mode	= $this->session->get( 'filter.work.mission.mode' ).'.';
		return $this->filterKeyPrefix.$mode;
	}

	/**
	 * @todo	remove this because all methods receiver userId and this is using roleId from session
	 */
	protected function hasFullAccess(){
		return $this->env->getAcl()->hasFullAccess( $this->session->get( 'roleId' ) );
	}

	public function import(){
		$this->checkIsEditor();
		$file	= $this->env->getRequest()->get( 'serial' );
		if( $file['error'] != 0 ){
			$handler	= new Net_HTTP_UploadErrorHandler();
			$this->messenger->noteError( 'Upload-Fehler: '.$handler->getErrorMessage( $file['error'] ) );
		}
		else{
			$gz			= FS_File_Reader::load( $file['tmp_name'] );
			$serial		= @gzinflate( substr( $gz, 10, -8 ) );
			$missions	= @unserialize( $serial );
			if( !$serial )
				$this->messenger->noteError( 'Das Entpacken der Daten ist fehlgeschlagen.' );
			else if( !$missions )
				$this->messenger->noteError( 'Keine Daten enthalten.' );
			else{
				$model	= new Model_Mission( $this->env );
				$model->truncate();
				foreach( $missions as $mission )
					$model->add( (array) $mission );
				$this->messenger->noteSuccess( 'Die Daten wurden importiert.' );
			}
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	Default action on this controller.
	 *	@access		public
	 *	@return		void
	 */
	public function index( $missionId = NULL ){
		if( trim( $missionId ) )
			$this->restart( 'view/'.$missionId, TRUE );

		$this->initFilters( $this->userId );
		$mode	= $this->session->get( 'filter.work.mission.mode' );
		if( $mode !== 'now' )
			$this->restart( './work/mission/'.$mode );

		$words			= (object) $this->getWords( 'index' );

		if( $this->request->has( 'view' ) )
			$this->session->set( 'work-mission-view-type', (int) $this->request->get( 'view' ) );

//		if( (int) $this->session->get( 'work-mission-view-type' ) == 1 )
//			$this->restart( './work/mission/calendar' );

		$this->addData( 'userId', $this->userId );
		$this->addData( 'viewType', (int) $this->session->get( 'work-mission-view-type' ) );

		$this->assignFilters();

		$this->setData( array(																		//  assign data to view
			'missions'		=> $this->getFilteredMissions( $this->userId ),							//  add user missions
			'userProjects'	=> $this->userProjects,													//  add user projects
			'users'			=> $this->userMap,														//  add user map
			'currentDay'	=> (int) $this->session->get( $this->filterKeyPrefix.'day' ),			//  set currently selected day
		) );
	}

	protected function initDefaultFilters(){
		if( $this->session->get( 'filter.work.mission.mode' ) === NULL )
			$this->session->set( 'filter.work.mission.mode', $this->defaultFilterValues['mode'] );
		if( !$this->session->get( $this->filterKeyPrefix.'types' ) )
			$this->session->set( $this->filterKeyPrefix.'types', $this->defaultFilterValues['types'] );
		if( !$this->session->get( $this->filterKeyPrefix.'priorities' ) )
			$this->session->set( $this->filterKeyPrefix.'priorities', $this->defaultFilterValues['priorities'] );
		if( !$this->session->get( $this->filterKeyPrefix.'states' ) ){
//			$tense		= $this->session->get( $this->filterKeyPrefix.'tense' );
			$states		= $this->defaultFilterValues['states'];
			$this->session->set( $this->filterKeyPrefix.'states', $states );
		}
		if( !$this->session->get( $this->filterKeyPrefix.'projects' ) )
			$this->session->set( $this->filterKeyPrefix.'projects', array_keys( $this->userProjects ) );
		if( !$this->session->get( $this->filterKeyPrefix.'workers' ) )
			$this->session->set( $this->filterKeyPrefix.'workers', array_keys( $this->userMap ) );
		if( $this->session->get( $this->filterKeyPrefix.'order' ) === NULL ){
			if( $this->session->get( $this->filterKeyPrefix.'direction' ) === NULL ){
//				$tense		= $this->session->get( $this->filterKeyPrefix.'tense' );
				$order		= $this->defaultFilterValues['order'];
				$direction	= $this->defaultFilterValues['direction'];
				$this->session->set( $this->filterKeyPrefix.'order', $order );
				$this->session->set( $this->filterKeyPrefix.'direction', $direction );
			}
		}
	}

	protected function initFilters( $userId ){
		if( !(int) $userId )
			return;
		if( !$this->session->getAll( 'filter.work.mission.', TRUE )->count() )
			$this->recoverFilters( $userId );

		//  --  DEFAULT SETTINGS  --  //
		$this->initDefaultFilters();

		//  --  GENERAL LOGIC CONDITIONS  --  //
		$mode	= $this->session->get( 'filter.work.mission.mode' );
		$this->logic->generalConditions['status']		= $this->defaultFilterValues['states'];
		switch( $mode ){
			case 'now':
				$this->logic->generalConditions['dayStart']	= '<'.date( "Y-m-d", time() + 7 * 24 * 60 * 60 );				//  @todo: kriss: calculation is incorrect
				break;
//			case 'future':
//				$this->logic->generalConditions['dayStart']	= '>='.date( "Y-m-d", time() + 6 * 24 * 60 * 60 );				//  @todo: kriss: calculation is incorrect
//				break;
		}
	}

	public function kanban(){
		$this->session->set( 'filter.work.mission.mode', 'kanban' );
		$this->restart( NULL, TRUE );
	}

	public function now(){
		$this->session->set( 'filter.work.mission.mode', 'now' );
		$this->restart( NULL, TRUE );
	}

	protected function recoverFilters( $userId ){
		$model	= new Model_Mission_Filter( $this->env );
		$serial	= $model->getByIndex( 'userId', $userId, array(), 'serial' );
//	print_m( $serial );
//	print_m( unserialize( $serial ) );
//	die;
//	$this->env->getMessenger()->noteNotice( '<xmp>'.$serial.'</xmp>' );
//		if( !strlen( $serial ) )
//			return;
		$serial	= $serial ? unserialize( $serial ) : NULL;
		if( is_array( $serial ) ){
			foreach( $serial as $key => $value )
				$this->session->set( 'filter.work.mission.'.$key, $value );
			$this->env->getMessenger()->noteNotice( 'Filter für Aufgaben aus der letzten Sitzung wurden reaktiviert.' );
			$this->restart( NULL, TRUE );
		}
	}

	public function removeDocument( $missionId, $missionDocumentId ){
		$this->logic->removeDocument( $missionDocumentId );
		$this->restart( 'edit/'.$missionId, TRUE );
	}

	protected function saveFilters( $userId ){
		$model		= new Model_Mission_Filter( $this->env );
		$serial		= serialize( $this->session->getAll( 'filter.work.mission.' ) );
		$data		= array( 'serial' => $serial, 'timestamp' => time() );
		$indices	= array( 'userId' => $userId );
		$filter		= $model->getByIndex( 'userId', $userId );
		if( $filter )
			$model->edit( $filter->missionFilterId, $data );
		else
			$model->add( $data + $indices );
	}

	public function setFilter( $name, $value = NULL, $set = FALSE, $onlyThisOne = FALSE ){
		$sessionPrefix	= $this->getModeFilterKeyPrefix();
		$storedValues	= $this->session->get( $sessionPrefix.$name );
		$newValues		= $value;
		if( is_array( $storedValues ) ){
			$newValues	= $storedValues;
			if( is_null( $value ) )																	//  no value given at all
				$newValues	= array();																//  resest values, will be set to all by controller
			else if( $onlyThisOne )																	//  otherwise: only set this value
				$newValues	= array( $value );														//  replace all by just this value
			else{																					//  otherwise: specific mode
				if( $set )																			//  new value to be set
					$newValues[]	= $value;														//  append new value
				else{																				//  stored value to be removed
					$pos = array_search( $value, $newValues );										//  find value position in stored values list
					if( $pos >= 0 )																	//  value is within stored values
						unset( $newValues[$pos] );													//  remove value
				}
			}
		}
		$this->session->set( $sessionPrefix.$name, $newValues );
		$this->saveFilters( $this->userId );
		if( $this->env->getRequest()->isAjax() ){
			$this->redirect( 'work/mission/ajaxRenderIndex' );
//			header( 'Content-Type: application/json' );
//			print( json_encode( TRUE ) );
//			exit;
		}
		$this->restart( NULL, TRUE );
	}

	public function setPriority( $missionId, $priority, $showMission = FALSE ){
		$this->checkIsEditor( $missionId );
		$data	= array();
		$this->model->edit( $missionId, array(														//  store in database
			'priority'		=> $priority,															//  - new priority
			'modifierId'	=> $this->userId,														//  - modifying user id
			'modifiedAt'	=> time(),																//  - modification time
		) );
		if( !$showMission )																			//  back to list
			$this->restart( NULL, TRUE );															//  jump to list
		$this->restart( 'edit/'.$missionId, TRUE );													//  otherwise jump to or stay in mission
	}

	public function setStatus( $missionId, $status, $showMission = FALSE ){
		$this->checkIsEditor( $missionId );
		$this->model->edit( $missionId, array(														//  store in database
			'status'		=> $status,																//  - new status
			'modifierId'	=> $this->userId,														//  - modifying user id
			'modifiedAt'	=> time(),																//  - modification time
		) );
		if( $status < 0 || !$showMission )															//  mission aborted/done or back to list
			$this->restart( NULL, TRUE );															//  jump to list
		$this->restart( 'edit/'.$missionId, TRUE );													//  otherwise jump to or stay in mission
	}

	public function testMail( $type, $send = FALSE ){
		switch( $type ){
			case "daily":																			//
				$modelUser		= new Model_User( $this->env );										//
				$modelMission	= new Model_Mission( $this->env );									//
				$user			= $modelUser->get( $this->userId );									//

				$groupings	= array( 'missionId' );													//  group by mission ID to apply HAVING clause
				$havings	= array(																//  apply filters after grouping
					'creatorId = '.(int) $user->userId,												//
					'workerId = '.(int) $user->userId,												//
				);
				if( $this->env->getModules()->has( 'Manage_Projects' ) ){							//  look for module
					$modelProject	= new Model_Project( $this->env );								//
					$userProjects	= $modelProject->getUserProjects( $user->userId );				//  get projects assigned to user
					if( $userProjects )																//  projects found
						$havings[]	= 'projectId IN ('.join( ',', array_keys( $userProjects ) ).')';//  add to HAVING clause
				}
				$havings	= array( join( ' OR ', $havings ) );									//  render HAVING clause

				//  --  TASKS  --  //
				$filters	= array(																//  task filters
					'type'		=> 0,																//  tasks only
					'status'	=> array( 0, 1, 2, 3 ),												//  states: new, accepted, progressing, ready
					'dayStart'	=> "<=".date( "Y-m-d", time() ),									//  present and past (overdue)
				);
				$order	= array( 'priority' => 'ASC' );
				$tasks	= $modelMission->getAll( $filters, $order, NULL, NULL, $groupings, $havings );	//  get filtered tasks ordered by priority

				//  --  EVENTS  --  //
				$filters	= array(																//  event filters
					'type'		=> 1,																//  events only
					'status'	=> array( 0, 1, 2, 3 ),												//  states: new, accepted, progressing, ready
					'dayStart'	=> "<=".date( "Y-m-d", time() ),									//  starting today
				);
				$order	= array( 'timeStart' => 'ASC' );
				$events	= $modelMission->getAll( $filters, $order, NULL, NULL, $groupings, $havings );	//  get filtered events ordered by start time

				if( !$events && !$tasks )															//  user has neither tasks nor events
					continue;																		//  do not send a mail, leave user alone

				$data		= array( 'user' => $user, 'tasks' => $tasks, 'events' => $events );		//  data for mail upcoming object
				$mail		= new Mail_Work_Mission_Daily( $this->env, $data );						//  create mail and populate data
				$content	= print( $mail->content );
				break;
			default:
				throw new InvalidArgumentException( 'Invalid mail type' );
		}
		print( $content );
		exit;
	}

	public function testMailNew( $missionId ){
		$data	= array(
            'mission'   => $this->model->get( $missionId ),
            'user'      => $this->userMap[$this->userId],
        );
		$mail	= new Mail_Work_Mission_New( $this->env, $data );
		print( $mail->renderBody( $data ) );
		die;
	}

	public function testMailUpdate( $missionId ){
		$missionOld		= $this->model->get( $missionId );
		$missionNew		= clone( $missionOld );
		$missionOld->status = 1;
		$missionNew->type = "1";
		$missionOld->priority = "1";
		$missionNew->dayStart	= date( "Y-m-d", strtotime( $missionNew->dayStart ) - 3600 * 24 );
		$data		= array(
			'missionBefore'	=> $missionOld,
			'missionAfter'	=> $missionNew,
			'user'			=> $this->userMap[$this->userId],
		);
		$mail	= new Mail_Work_Mission_Update( $this->env, $data );
		print( $mail->renderBody( $data ) );
		die;
	}

	public function view( $missionId ){
		$words		= (object) $this->getWords( 'edit' );

		$mission	= $this->model->get( $missionId );
		if( !$mission ){
			$this->messenger->noteError( $words->msgInvalidId );
			$this->restart( NULL, TRUE );
		}
		if( $this->useProjects ){																	//  @todo remove deprecated 'useProjects'
			if( !array_key_exists( $mission->projectId, $this->userProjects ) ){
				$this->messenger->noteError( $words->msgInvalidProject );
				$this->restart( NULL, TRUE );
			}
		}

/*		$mode	= $this->session->get( 'filter.work.mission.mode' );
		if( $mission->status < 0 || $mission->status > 3 ){
			if( in_array( $mode, array( 'now', 'future' ) ) )
				$this->session->set( 'filter.work.mission.mode', 'archive' );
		}
		else if( $this->session->get( 'filter.work.mission.mode' ) == 'archive' ){
			$this->session->set( 'filter.work.mission.mode', 'now' );
		}*/

		$title		= $this->request->get( 'title' );
		$dayStart	= $this->request->get( 'dayStart' );
		$dayEnd		= $this->request->get( 'dayEnd' );
		if( $this->request->get( 'type' ) == 0 ){
			$dayStart	= $this->logic->getDate( $this->request->get( 'dayWork' ) );
			$dayEnd		= $this->request->get( 'dayDue' ) ? $this->logic->getDate( $this->request->get( 'dayDue' ) ) : NULL;
		}
		$modelUser	= new Model_User( $this->env );
		$mission->creator	= array_key_exists( $mission->creatorId, $this->userMap ) ? $this->userMap[$mission->creatorId] : NULL;
		$mission->modifier	= array_key_exists( $mission->modifierId, $this->userMap ) ? $this->userMap[$mission->modifierId] : NULL;
		$mission->worker	= array_key_exists( $mission->workerId, $this->userMap ) ? $this->userMap[$mission->workerId] : NULL;
		$mission->versions	= $this->logic->getVersions( $missionId );
		$this->addData( 'mission', $mission );
		$this->addData( 'users', $this->userMap );
		$missionUsers		= array( $mission->creatorId => $mission->creator );
		if( $mission->workerId )
			$missionUsers[$mission->workerId]	= $mission->worker;

		if( $this->useProjects ){																	//  @todo remove deprecated 'useProjects'
			$model		= new Model_Project( $this->env );
			foreach( $model->getProjectUsers( (int) $mission->projectId ) as $user )
				$missionUsers[$user->userId]	= $user;
			$this->addData( 'userProjects', $this->userProjects );
			$mission->project	= $model->get( (int) $mission->projectId );
		}
		$this->addData( 'missionUsers', $missionUsers );

		if( $this->useIssues ){
			$this->env->getLanguage()->load( 'work/issue' );
			$this->addData( 'wordsIssue', $this->env->getLanguage()->getWords( 'work/issue' ) );
		}

		$model		= new Model_Mission_Document( $this->env );
		$orders		= array( 'modifiedAt' => 'DESC', 'createdAt' => 'DESC' );
		$documents	= $model->getAllByIndex( 'missionId', $missionId, $orders );
		$this->addData( 'documents', $documents );
	}

	public function viewDocument( $missionId, $missionDocumentId ){
		$this->deliverDocument( $missionId, $missionDocumentId, FALSE );
	}
}
?>
