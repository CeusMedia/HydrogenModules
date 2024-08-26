<?php
class Job_Work_Mission extends Job_Abstract
{
	protected Logic_Mail $logicMail;
	protected string $language;
	protected Model_Mission_Change $modelChange;
	protected Model_Mission_Document $modelDocument;
	protected Model_Mission_Version $modelVersion;
	protected Model_Mission $modelMission;
	protected Model_Project $modelProject;
	protected Model_User $modelUser;
	protected bool $useSettings;

	/**
	 *	@return		int
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function informAboutChanges(): int
	{
		$language		= $this->language;														//  @todo get user language instead of current language
		$count			= 0;																	//  init mail counter
		foreach( $this->modelChange->getAll( [], [], [0, 10] ) as $change ){					//  iterate mission changes
			$missionNew	= $this->modelMission->get( $change->missionId );						//  get current mission data
			if( !$missionNew ){																	//  mission is not existing anymore
				$this->modelChange->remove( $change->missionChangeId );							//  remove change
				continue;
			}
			switch( strtolower( $change->type ) ){												//  which change type?
				case 'new':																		//  inform about new mission
					$receivers	= $this->getUpdateMailReceivers(								//  get mail receivers
						[(int) $missionNew->projectId],											//  of mission project
						[$missionNew->workerId],												//  include project worker
						[$change->userId]														//  exclude change maker
					);
					foreach( $receivers as $receiverId => $user ){								//  iterate mail receivers
						$mail	= new Mail_Work_Mission_New( $this->env, [						//  prepare mail
							'mission'		=> $missionNew,										//  provide current mission data
							'user'			=> $user,											//  provide receiver user data
							'modifier'		=> $this->modelUser->get( $change->userId ),		//  provide modifying user data
						] );
						$this->logicMail->handleMail( $mail, $user, $language );				//  send mail to current receiver
						$count++;																//  count sent mail
					}
					break;
				case 'update':																	//  inform about mission update
					$missionOld	= unserialize( $change->data );									//  get old mission data
					$receivers	= $this->getUpdateMailReceivers(								//  get mail receivers
						[(int) $missionNew->projectId, (int) $missionOld->projectId],			//  of old and new project
						[$missionNew->workerId, $missionOld->workerId],							//  include old and new project worker
						[$change->userId]														//  exclude change maker
					);
					foreach( $receivers as $receiverId => $user ){								//  iterate mail receivers
						$mail	= new Mail_Work_Mission_Update( $this->env, [					//  prepare mail
							'missionBefore'	=> $missionOld,										//  provide old mission data
							'missionAfter'	=> $missionNew,										//  provide new mission data
							'user'			=> $user,											//  provide receiver user data
							'modifier'		=> $this->modelUser->get( $change->userId ),		//  provide modifying user data
						] );
						$this->logicMail->handleMail( $mail, $user, $language );				//  send mail to current receiver
						$count++;																//  count sent mail
					}
					break;
			}
			$this->modelChange->remove( $change->missionChangeId );								//  remove change
		}
		$this->out( date( "Y-m-d H:i:s" ).' sent '.$count.' mails.' );					//  note sent mails
		return $count;																			//  return number of sent mails
	}

	/**
	 *	Calls logic to update user activity states.
	 *	Depending on activity timeout rules and user interface, users will be marked as "idle" or logout out after some time.
	 *	Run this job every minute to two.
	 *	@access		public
	 *	@todo		implement this job
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function mailDaily(): void
	{
		$count			= 0;
		$activeUsers	= $this->modelUser->getAll( ['status' => '> 0'] );						//  get all active users
		foreach( $activeUsers as $user ){														//  iterate found users
			if( $this->sendDailyMailOfUser( $user ) )											//  try to send daily mail
				$count++;																		//  count on success
		}
		$this->out( 'Sent '.$count.' mails.' );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function cleanup(): void
	{
		$missionIds		= array_unique( array_values( $this->modelVersion->getAll(
			[],
			['timestamp' => 'ASC'],
			[],
			['missionId']
		) ) );
		if( $missionIds ){
			$missionIds	= $this->modelMission->getAll( [
				'status'	=> [
					Model_Mission::STATUS_ABORTED,
					Model_Mission::STATUS_REJECTED,
					Model_Mission::STATUS_FINISHED,
				],
				'missionId'	=> $missionIds,
			], [], [], ['missionId'] );

			if( $this->dryMode ){
				$this->out( 'DRY RUN - no changes will be made.' );
				$this->out( 'Would remove content versions of '.count( $missionIds ).' closed missions.' );
			}
			else{
				$count	= 0;
				foreach( $missionIds as $nr => $missionId ){
					$count	+= $this->modelVersion->removeByIndex( 'missionId', $missionId );
					$this->showProgress( $nr + 1, count( $missionIds ) );
				}
				if( $missionIds )
					$this->out();
				$this->out( '- Removed '.$count.' content versions of '.count( $missionIds ).' closed missions.' );
			}
		}

		$projects		= [];
		foreach( $this->modelProject->getAll() as $project )
			$projects[$project->projectId]	= $project;
		$projectIds	= array_keys( $projects );

		$query		= 'SELECT missionId FROM missions WHERE projectId NOT IN ('.join( ',', $projectIds ).')';
		$result		= $this->env->getDatabase()->query( $query );
		$missions	= $result->fetchAll( PDO::FETCH_OBJ );
		if( $missions ){
			foreach( $missions as $mission ){
				$missionId		= $mission->missionId;
				$nrChanges		= $this->modelChange->count( ['missionId' => $missionId] );
				$nrVersions		= $this->modelVersion->count( ['missionId' => $missionId] );
				$nrDocuments	= $this->modelDocument->count( ['missionId' => $missionId] );
				$this->out( 'Mission: '.$missionId.' => ('.$nrChanges.' changes, '.$nrDocuments.' documents, '.$nrVersions.' versions)' );
				if( !$this->dryMode ){
					$this->modelChange->removeByIndex( 'missionId', $missionId );
					$this->modelVersion->removeByIndex( 'missionId', $missionId );
					$this->modelDocument->removeByIndex( 'missionId', $missionId );
					$this->modelMission->remove( $missionId );
				}
			}
		}
		$this->out( '- Removed '.count( $missions ).' missions not related to projects.' );

		$modelWorkTimer		= new Model_Work_Timer( $this->env );
		$query				= 'SELECT * FROM work_timers WHERE projectId NOT IN ('.join( ',', $projectIds ).')';
		$result				= $this->env->getDatabase()->query( $query );
		$timers				= $result->fetchAll( PDO::FETCH_OBJ );
		$countTimerMoved	= 0;
		$countTimerRemoved	= 0;
		if( $timers ){
			foreach( $timers as $timer ){
				if( 'Work_Missions' === $timer->module ){
					$mission	= $this->modelMission->get( $timer->moduleId );
					if( $mission ){
						$modelWorkTimer->edit( $timer->workTimerId, [
							'moduleId'	=> $mission->missionId,
							'projectId'	=> $mission->projectId,
						] );
						$countTimerMoved++;
					}
					else{
						$modelWorkTimer->remove( $timer->workTimerId );
						$countTimerRemoved++;
					}
				}
			}
		}
		$this->out( '- Timers without projects: '.$countTimerMoved.' moved, '.$countTimerRemoved.' removed.' );
	}

	//  --  PROTECTED  --  //

	protected function __onInit(): void
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicMail	= Logic_Mail::getInstance( $this->env );
		$this->language		= $this->env->getLanguage()->getLanguage();							//  @deprecated if each mail is sent in user language
		$this->modelChange		= new Model_Mission_Change( $this->env );							//  get mission changes model
		$this->modelDocument	= new Model_Mission_Document( $this->env );
		$this->modelVersion		= new Model_Mission_Version( $this->env );
		$this->modelMission		= new Model_Mission( $this->env );									//  get mission model
		$this->modelProject		= new Model_Project( $this->env );									//  get project model
		$this->modelUser		= new Model_User( $this->env );										//  get user model
		$this->useSettings		= $this->env->getModules()->has( 'Manage_My_User_Settings' );			//  user settings are enabled
	}

	/**
	 *	Get mail receivers of update mails.
	 *	@access		protected
	 *	@param		array		$projectIds		List of project IDs to collect user of
	 *	@param		array		$includes		List of user IDs to include
	 *	@param		array		$excludes		List of user IDs to exclude
	 *  @return		array		List of mail receiving users
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function getUpdateMailReceivers( array $projectIds, array $includes = [], array $excludes = [] ): array
	{
		$list	= [];																		//  prepare empty user list
		$projectIds	= array_unique( $projectIds );
		foreach( $projectIds as $projectId )												//  iterate given projects IDs
			foreach( $this->modelProject->getProjectUsers( (int) $projectId ) as $user )	//  iterate project users
				$list[(int) $user->userId]	= $user;										//  enlist user
		foreach( $includes as $userId )															//  iterate users to include
			if( !array_key_exists( (int) $userId, $list ) )										//  user is not in list yet
				if( $user = $this->modelUser->get( $userId ) )									//  user exists
					$list[(int) $userId]	= $user;											//  enlist user
		foreach( $excludes as $userId )															//  iterate users to exclude
			if( array_key_exists( (int) $userId, $list ) )										//  user is in list
				unset( $list[(int) $userId] );													//  remove user from list
		$users			= [];																//  prepare final user list
		$config			= $this->env->getConfig();												//  get default config
		foreach( $list as $userId => $user ){													//  iterate so far listed users
			if( $this->useSettings )															//  user settings are enabled
				$config	= Model_User_Setting::applyConfigStatic( $this->env, $userId );			//  apply user settings
			$user->config	= $config->getAll( 'module.work_missions.mail.', TRUE );			//  store module settings of user
			if( (int) $user->status > 0 )														//  user is enabled
				if( strlen( trim( $user->email ) ) > 0 )										//  user as mail address
					if( (int) $user->config->get( 'active' ) )									//  user mailing is enabled
						if( (int) $user->config->get( 'changes' ) )								//  changes mail is enabled
							$users[$userId]	= $user;											//  add user to final user list
		}
		return $users;																			//  return final user list
	}

	/**
	 *	@param		object		$user
	 *	@return		true|void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function sendDailyMailOfUser( object $user )
	{
		if( $user->userId != 4 )
			return;
		if( !strlen( trim( $user->email ) ) )													//  no mail address configured for user
			return;																				//  @todo  handle this exception state!
		$config			= $this->env->getConfig();
		$language		= $this->language;														//  @todo get user language instead of current language
		if( $this->useSettings )
			$config	= Model_User_Setting::applyConfigStatic( $this->env, $user->userId );
		$config		= $config->getAll( 'module.work_missions.mail.', TRUE );
		$isActiveUser	= (int) $user->status >= Model_User::STATUS_ACTIVE;						//  user is active and has mail address
		$isMailReceiver	= $config->get( 'active' ) && $config->get( 'daily' );		//  mails are enabled
		$isSendHour		= (int) $config->get( 'daily.hour' ) === (int) date( "H" );	//  the future is now
		if( !( $isActiveUser && $isMailReceiver && $isSendHour ) )								//
			return;
		$groupings	= ['missionId'];															//  group by mission ID to apply HAVING clause
		$havings	= [																			//  apply filters after grouping
			'creatorId = '.(int) $user->userId,													//
			'workerId = '.(int) $user->userId,													//
		];
		$userProjects	= $this->modelProject->getUserProjects( $user->userId );			//  get projects assigned to user
		if( $userProjects )																	//  projects found
			$havings[]	= 'projectId IN ('.join( ',', array_keys( $userProjects ) ).')';	//  add to HAVING clause
		$havings	= [join( ' OR ', $havings )];										//	render HAVING clause

		//  --  TASKS  --  //
			$filters	= [																		//  task filters
			'type'		=> 0,																	//  tasks only
			'status'	=> [0, 1, 2, 3],														//  states: new, accepted, progressing, ready
			'dayStart'	=> '<= '.date( "Y-m-d", time() ),								//  present and past (overdue)
		];
		$order	= ['priority' => 'ASC'];
		$tasks	= $this->modelMission->getAll( $filters, $order, [], [], $groupings, $havings );	//  get filtered tasks ordered by priority

		//  --  EVENTS  --  //
		$filters	= [																			//  event filters
			'type'		=> 1,																	//  events only
			'status'	=> [0, 1, 2, 3],														//  states: new, accepted, progressing, ready
			'dayStart'	=> '<= '.date( "Y-m-d", time() ),								//  starting today
		];
		$order	= ['timeStart' => 'ASC'];
		$events	= $this->modelMission->getAll( $filters, $order, [], [], $groupings, $havings );	//  get filtered events ordered by start time

		if( !$events && !$tasks )																//  user has neither tasks nor events
			return;																				//  do not send a mail, leave user alone

		$mail	= new Mail_Work_Mission_Daily( $this->env, [								//  create mail and populate data
			'user'		=> $user,
			'tasks'		=> $tasks,
			'events'	=> $events
		] );
		$this->logicMail->handleMail( $mail, $user, $language );
		return TRUE;
	}
}
