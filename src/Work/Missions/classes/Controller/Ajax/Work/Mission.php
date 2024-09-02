<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\PartitionSession;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;
use CeusMedia\HydrogenFramework\Environment\Resource\Acl\Abstraction as AclResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Ajax_Work_Mission extends AjaxController
{
	protected HttpRequest $request;
	protected PartitionSession $session;
	protected MessengerResource $messenger;
	protected AclResource $acl;
	protected Logic_Work_Mission $logic;
	protected Logic_Authentication $logicAuth;
	protected ?Logic_Database_Lock $lock	= NULL;
	protected Logic_Project $logicProject;
	protected Model_Mission $model;
	protected array $userProjects;
	protected array $userMap;
	protected bool $isEditor;
	protected bool $isViewer;
	protected bool $useIssues;
	protected bool $useTimer;
	protected string $filterKeyPrefix		= 'filter.work.mission.';
	protected string $userId				= '0';
	protected string $userRoleId			= '0';
	protected string $contentFormat;
	protected Dictionary $moduleConfig;

	protected int|string $panelId	= 0;
	protected array $tasks			= [];
	protected array $events			= [];
	protected array $projects		= [];
	protected array $words			= [];

	/**
	 *	Moves a mission by several days or to a given date.
	 *	Receives date or day difference using POST.
	 *	A day difference can be formatted like +2 or -2.
	 *	Moving a task mission will only affect start date but end date will remain unchanged.
	 *	Moving an event mission will affect start and end date.
	 *	If called using AJAX list rendering is triggered.
	 *	@access		public
	 *	@param		int|string		$missionId		ID of mission to move in time
	 *	@return		void
	 *	@todo		enable this feature for AJAX called EXCEPT gid list
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function changeDay( int|string $missionId ): void
	{
		$date		= trim( $this->request->get( 'date' ) );
		$mission	= $this->model->get( $missionId );
		$data		= [
			'modifierId'	=> $this->userId,
			'modifiedAt'	=> time(),
		];
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
			/** @noinspection PhpUnhandledExceptionInspection */
			$date	= new DateTime( $mission->dayStart );
			$data['dayStart'] = $date->modify( $change )->format( "Y-m-d" );
			if( $mission->dayEnd ){													//  mission has a duration
				if( Model_Mission::TYPE_EVENT === (int) $mission->type ){											//  mission is an event, not a task
					/** @noinspection PhpUnhandledExceptionInspection */
					$date	= new  DateTime( $mission->dayEnd );					//  take end timestamp and ...
					$data['dayEnd'] = $date->modify( $change )->format( "Y-m-d" );  //  ... store new moved end date
				}
			}
			$this->model->edit( $missionId, $data );
			$this->logic->noteChange( 'update', $missionId, $mission, $this->userId );
		}
		$this->renderIndex();
	}

	/**
	 *	@todo			check if this method is needed anymore
	 *	@throws		JsonException
	 */
	public function checkForUpdate( int|string $userId ): void
	{
		if( file_exists( "update-".$userId ) ){
			@unlink( "update-".$userId );
			$this->respondData( TRUE );
		}
		$this->respondData( FALSE );
	}

	/**
	 *	@param		string		$projectId
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function getProjectUsers( string $projectId ): void
	{
		$list	= [];
		$model	= new Model_Project( $this->env );
		$users	= $model->getProjectUsers( (int) $projectId );
		if( array_key_exists( $this->userId, $users ) || $this->logic->hasFullAccess() ){
			foreach( $users as $user )
				$list[$user->username]    = $user;
		}
		ksort( $list );
		$this->respondData( array_values( $list ) );
	}

	/**
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function renderContent(): void
	{
		$content	= $this->env->getRequest()->get( 'content' );
		$html		= View_Helper_Markdown::transformStatic( $this->env, $content );
		$this->respondData( $html );
	}

	public function renderDashboardPanel( string $panelId ): string
	{
		$logic		= Logic_Work_Mission::getInstance( $this->env );
		$this->panelId		= $panelId;
		switch( $panelId ){
			case 'work-mission-my-tasks':
				$conditions		= [
					'status'	=> [0, 1, 2, 3],
					'type'		=> Model_Mission::TYPE_TASK,
					'dayStart'	=> '<= '.date( 'Y-m-d', time() ),
//					'dayEnd'	=> '>= '.date( 'Y-m-d', time() ),
					'workerId'	=> $this->userId,
				];
				$orders		= [
					'priority'	=> 'ASC',
					'title'		=> 'ASC',
				];
				$tasks	= $logic->getUserMissions( $this->userId, $conditions, $orders );
				$this->tasks	= $tasks;
				break;
			case 'work-mission-my-today':
			default:
				$conditions	= [
					'type'			=> Model_Mission::TYPE_EVENT,
					'status'		=> [0, 1, 2, 3],
					'dayStart'		=> date( 'Y-m-d' ),
				];
				$orders	= ['timeStart' => 'ASC'];
				$events	= $logic->getUserMissions( $this->userId, $conditions, $orders );
				$this->events	= $events;
				break;
		}
		try{
			switch( $this->panelId ){
				case 'work-mission-my-tasks':
					$helper		= new View_Helper_Work_Mission_Dashboard_MyTasks( $this->env );
					$helper->setTasks( $this->tasks );
					break;
				case 'work-mission-my-today':
				default:
					$helper		= new View_Helper_Work_Mission_Dashboard_MyEvents( $this->env );
					$helper->setEvents( $this->events );
					break;
			}
			$helper->setProjects( $this->projects );
			return $this->respond( $helper->render() );
		}
		catch( Exception $e ){
			return $this->respondException( $e );
		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		JsonException
	 */
	public function renderIndex(): void
	{
		$mode	= $this->session->get( $this->filterKeyPrefix.'mode' );
		if( $mode && $mode !== 'now' )
			$this->env->restart( 'ajax/work/mission/'.$mode.'/renderIndex' );
//			$this->redirect( 'work/mission/'.$mode, 'ajaxRenderIndex', func_get_args() );		//  @todo replace redirect but keep AJAX request in mind
		else{
			$words		= $this->getWords();

			$day		= (int) $this->session->get( $this->filterKeyPrefix.'day' );

			$missions	= $this->logic->getFilteredUserMissions( $this->userId, $this->filterKeyPrefix );
			$missions	= array_slice( $missions, 0, 100 );										//  @todo	 make configurable

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

			$data		= [
				'day'		=> $day,
				'items'		=> $allDayMissions[$day],//$listLarge->getDayMissions( $day ),
				'count'		=> count( $allDayMissions[$day] ),//$listLarge->getDayMissions( $day ) ),
				'total'		=> $total,
				'buttons'	=> [
					'large'	=> $buttonsLarge->render(),
					'small'	=> $buttonsSmall->render(),
				],
				'lists'		=> [
					'large'	=> $listLarge->renderDayList( 1, $day, TRUE, TRUE, FALSE, TRUE ),
					'small'	=> $listSmall->renderDayList( 1, $day, TRUE, TRUE, FALSE, !TRUE )
				],
				'filters'	=> $this->session->getAll( $this->filterKeyPrefix.$mode.'.' ),
			];
			$this->respondData( $data );
		}
	}

	/**
	 *	@param		int|string		$missionId
	 *	@param		$version
	 *	@param		$versionCompare
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function renderMissionContent( int|string $missionId, $version = NULL, $versionCompare = NULL ): void
	{
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
			$this->respondData( $content );
		}
		catch( Exception $e ){
			$this->respondError( 0, $e->getMessage() );
		}
	}

	/**
	 *	@param		int|string		$missionId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		JsonException
	 */
	public function saveContent( int|string $missionId ): void
	{
		$content	= $this->env->getRequest()->get( 'content' );
		$this->model->edit( $missionId, [														//  store in database
			'content'		=> $content,															//  - new content
			'modifierId'	=> $this->userId,														//  - modifying user id
			'modifiedAt'	=> time(),																//  - modification time
		], FALSE );																					//  without striping tags
		$html		= View_Helper_Markdown::transformStatic( $this->env, $content );
		$this->respondData( $html );
	}

	/**
	 *	@param		string		$day
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		JsonException
	 */
	public function selectDay( string $day ): void
	{
		$this->session->set( $this->filterKeyPrefix.'day', (int) $day );
		$this->renderIndex();
	}

	/**
	 *	@param		string		$name
	 *	@param		$value
	 *	@param		bool		$set
	 *	@param		bool		$onlyThisOne
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 * @todo	enable for AJAX
	 */
	public function setFilter( string $name, $value = NULL, bool $set = FALSE, bool $onlyThisOne = FALSE ): void
	{
		$sessionPrefix	= $this->getModeFilterKeyPrefix();
		$storedValues	= $this->session->get( $sessionPrefix.$name );
		$newValues		= $value;
		if( is_array( $storedValues ) ){
			$newValues	= $storedValues;
			if( is_null( $value ) )																	//  no value given at all
				$newValues	= [];																	//  reset values, will be set to all by controller
			else if( $onlyThisOne )																	//  otherwise: only set this value
				$newValues	= [$value];																//  replace all by just this value
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
		$this->env->restart( 'ajax/work/mission/renderIndex' );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->acl			= $this->env->getAcl();

		$this->model		= new Model_Mission( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicProject	= Logic_Project::getInstance( $this->env );
		$this->logic		= Logic_Work_Mission::getInstance( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicAuth	= Logic_Authentication::getInstance( $this->env );

		$this->isEditor		= $this->acl->has( 'work/mission', 'edit' );
		$this->isViewer		= $this->acl->has( 'work/mission', 'view' );
		$this->useIssues	= $this->env->getModules()->has( 'Work_Issues' );
		$this->useTimer		= $this->env->getModules()->has( 'Work_Timer' );

		if( $this->logicAuth->isAuthenticated() ){
			$this->userId		= $this->logicAuth->getCurrentUserId();
			$this->userRoleId	= $this->logicAuth->getCurrentRoleId();
		}

		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.work_missions.', TRUE );
		$this->contentFormat	= $this->moduleConfig->get( 'format' );

//		if( !$this->userId || !$this->isViewer )
//			$this->restart( NULL, FALSE, 401 );

		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicProject	= Logic_Project::getInstance( $this->env );
		$this->userMap		= $this->logicProject->getCoworkers( $this->userId, NULL, TRUE );

		//  @todo	 DO NOT DO THIS!!! (badly scaling)
//		$model			= new Model_User( $this->env );
//		foreach( $model->getAll() as $user )
//			$this->userMap[$user->userId]	= $user;

/*		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'useTimer', $this->useTimer );
		$this->addData( 'useIssues', $this->useIssues );
		$this->addData( 'acl', $this->acl );
		$this->addData( 'userId', $this->userId );
		$this->addData( 'userRoleId', $this->userRoleId );*/

		$this->userProjects		= $this->logic->getUserProjects( $this->userId, TRUE );
		if( $this->logic->hasFullAccess() )
			$this->userProjects		= $this->logic->getUserProjects( $this->userId );
		$this->projects	= $this->userProjects;
		if( $this->env->getModules()->has( 'Resource_Database_Lock' ) )
			$this->lock	= new Logic_Database_Lock( $this->env );

		$this->words	= $this->env->getLanguage()->load( 'work/mission' );

//		$this->env->getModules()->callHook( 'Test', 'test', [] );
	}


	protected function getModeFilterKeyPrefix(): string
	{
		$mode	= '';
		if( $this->session->get( $this->filterKeyPrefix.'mode' ) !== 'now' )
			$mode	= $this->session->get( $this->filterKeyPrefix.'mode' ).'.';
		return $this->filterKeyPrefix.$mode;
	}

	protected function getWords( ?string $topic = NULL )
	{
		$words	= $this->env->getLanguage()->getWords( 'work/mission' );
		if( '' === ( $topic ?? '' ) )
			return $words['topic'];
		return $words;
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function saveFilters( int|string $userId ): void
	{
		$model		= new Model_Mission_Filter( $this->env );
		$serial		= serialize( $this->session->getAll( $this->filterKeyPrefix ) );
		$data		= ['serial' => $serial, 'timestamp' => time()];
		$indices	= ['userId' => $userId];
		$filter		= $model->getByIndex( 'userId', $userId );
		if( $filter )
			$model->edit( $filter->missionFilterId, $data );
		else
			$model->add( $data + $indices );
	}}