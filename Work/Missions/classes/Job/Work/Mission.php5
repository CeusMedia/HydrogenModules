<?php
class Job_Work_Mission extends Job_Abstract{

	protected $logicMail;
	protected $language;
	protected $modelChange;
	protected $modelMission;
	protected $modelProject;
	protected $modelUser;
	protected $useSettings;


	protected function __onInit(){
		$this->logicMail	= new Logic_Mail( $this->env );
		$this->language		= $this->env->getLanguage()->getLanguage();							//  @deprecated if each mail is sent in user language
		$this->modelChange	= new Model_Mission_Change( $this->env );							//  get mission changes model
		$this->modelMission	= new Model_Mission( $this->env );									//  get mission model
		$this->modelProject	= new Model_Project( $this->env );									//  get project model
		$this->modelUser	= new Model_User( $this->env );										//  get user model
		$this->useSettings	= $this->env->getModules()->has( 'Manage_My_User_Settings' );			//  user settings are enabled
	}



	/**
	 *	Get mail receivers of update mails.
	 *	@access		protected
	 *	@param		array		$projectsIds		List of project IDs to collect user of
	 *	@param		array		$includes			List of user IDs to include
	 *	@param		array		$excludes			List of user IDs to exclude
	 *  @return		array		List of mail receiving users
	 */
	protected function getUpdateMailReceivers( $projectIds, $includes = array(), $excludes = array() ){
		$list	= array();																		//  prepare empty user list
		$projectIds	= array_unique( $projectIds );
		if( $this->env->getModules()->has( 'Manage_Projects' ) ){								//  projects are enabled
			foreach( $projectIds as $projectId )												//  iterate given projects IDs
				foreach( $this->modelProject->getProjectUsers( (int) $projectId ) as $user )	//  iterate project users
					$list[(int) $user->userId]	= $user;										//  enlist user
		}
		foreach( $includes as $userId )															//  iterate users to include
			if( !array_key_exists( (int) $userId, $list ) )										//  user is not in list yet
				if( $user = $this->modelUser->get( $userId ) )									//  user exists
					$list[(int) $userId]	= $user;											//  enlist user
		foreach( $excludes as $userId )															//  iterate users to exclude
			if( array_key_exists( (int) $userId, $list ) )										//  user is in list
				unset( $list[(int) $userId] );													//  remove user from list
		$users			= array();																//  prepare final user list
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

    public function informAboutChanges(){
		$language		= $this->language;														//  @todo get user language instead of current language
		$count			= 0;																	//  init mail counter
		foreach( $this->modelChange->getAll() as $change ){										//  iterate mission changes
			$missionNew	= $this->modelMission->get( $change->missionId );						//  get current mission data
			switch( strtolower( $change->type ) ){												//  which change type?
				case 'new':																		//  inform about new mission
					$receivers	= $this->getUpdateMailReceivers(								//  get mail receivers
						array( (int) $missionNew->projectId ),									//  of mission project
						array( $missionNew->workerId ),											//  include project worker
						array( $change->userId )												//  exclude change maker
					);
					foreach( $receivers as $receiverId => $user ){								//  iterate mail receivers
						$mail	= new Mail_Work_Mission_New( $this->env, array(					//  prepare mail
							'mission'		=> $missionNew,										//  provide current mission data
							'user'			=> $user,											//  provide receiver user data
							'modifier'		=> $this->modelUser->get( $change->userId ),		//  provide modyifing user data
						) );
						$this->logicMail->handleMail( $mail, $user, $language );				//  send mail to current receiver
						$count++;																//  count sent mail
					}
					break;
				case 'update':																	//  inform about mission update
					$missionOld	= unserialize( $change->data );									//  get old mission data
					$receivers	= $this->getUpdateMailReceivers(								//  get mail receivers
						array( (int) $missionNew->projectId, (int) $missionOld->projectId ),	//  of old and new project
						array( $missionNew->workerId, $missionOld->workerId ),					//  include old and new project worker
						array( $change->userId )												//  exclude change maker
					);
					foreach( $receivers as $receiverId => $user ){								//  iterate mail receivers
						$mail   = new Mail_Work_Mission_Update( $this->env, array(				//  prepare mail
							'missionBefore'	=> $missionOld,										//  provide old mission data
							'missionAfter'	=> $missionNew,										//  provide new mission data
							'user'			=> $user,											//  provide receiver user data
							'modifier'		=> $this->modelUser->get( $change->userId ),		//  provide modifying user data
						) );
						$this->logicMail->handleMail( $mail, $user, $language );				//  send mail to current receiver
						$count++;																//  count sent mail
					}
					break;
			}
			$this->modelChange->remove( $change->missionChangeId );								//  remove change
		}
		$this->out( date( "Y-m-d H:i:s" ).' sent '.$count.' mails.' );							//  note sent mails
		return $count;																			//  return number of sent mails
	}

	/**
	 *	Calls logic to update user activity states.
	 *	Depending on activity timeout rules and user interface, users will be marked as "idle" or logout out after some time.
	 *	Run this job every minute to two.
	 *	@access		public
	 *	@todo		implement this job
	 *	@return		void
	 */
	public function mailDaily(){
		$count			= 0;
		$activeUsers	= $this->modelUser->getAll( array( 'status' => '>0' ) );						//  get all active users
		foreach( $activeUsers as $user ){														//  iterate found users
			if( $this->sendDailyMailOfUser( $user ) )											//  try to send daily mail
				$count++;																		//  count on success
		}
		$this->out( 'Sent '.$count.' mails.' );
	}

	protected function sendDailyMailOfUser( $user ){
		if( $user->userId != 4 )
			return;
		if( !strlen( trim( $user->email ) ) )													//  no mail address configured for user
			return;																				//  @todo kriss: handle this exception state!
		$config			= $this->env->getConfig();
		$language		= $this->language;														//  @todo get user language instead of current language
		if( $this->useSettings )
			$config	= Model_User_Setting::applyConfigStatic( $this->env, $user->userId );
		$config		= $config->getAll( 'module.work_missions.mail.', TRUE );
		$isActiveUser	= (int) $user->status > 0 && strlen( trim( $user->email ) );			//  user is active and has mail address
		$isMailReceiver	= $config->get( 'active' ) && $config->get( 'changes' );				//  mails are enabled
		$isSendHour		= 1 || (int) $config->get( 'daily.hour' ) === (int) date( "H" );				//  the future is now
		if( !( $isActiveUser && $isMailReceiver && $isSendHour ) )								//
			return;
		$groupings	= array( 'missionId' );														//  group by mission ID to apply HAVING clause
		$havings	= array(																	//  apply filters after grouping
			'creatorId = '.(int) $user->userId,													//
			'workerId = '.(int) $user->userId,													//
		);
		$userProjects	= $this->modelProject->getUserProjects( $user->userId );			//  get projects assigned to user
		if( $userProjects )																	//  projects found
			$havings[]	= 'projectId IN ('.join( ',', array_keys( $userProjects ) ).')';	//  add to HAVING clause
		$havings	= array( join( ' OR ', $havings ) );										//	render HAVING clause

		//  --  TASKS  --  //
			$filters	= array(																//  task filters
			'type'		=> 0,																	//  tasks only
			'status'	=> array( 0, 1, 2, 3 ),													//  states: new, accepted, progressing, ready
			'dayStart'	=> "<=".date( "Y-m-d", time() ),										//  present and past (overdue)
		);
		$order	= array( 'priority' => 'ASC' );
		$tasks	= $this->modelMission->getAll( $filters, $order, NULL, NULL, $groupings, $havings );	//  get filtered tasks ordered by priority

		//  --  EVENTS  --  //
		$filters	= array(																	//  event filters
			'type'		=> 1,																	//  events only
			'status'	=> array( 0, 1, 2, 3 ),													//  states: new, accepted, progressing, ready
			'dayStart'	=> "<=".date( "Y-m-d", time() ),										//  starting today
		);
		$order	= array( 'timeStart' => 'ASC' );
		$events	= $this->modelMission->getAll( $filters, $order, NULL, NULL, $groupings, $havings );	//  get filtered events ordered by start time

		if( !$events && !$tasks )																//  user has neither tasks nor events
			return;																				//  do not send a mail, leave user alone

		$mail	= new Mail_Work_Mission_Daily( $this->env, array(								//  create mail and populate data
			'user'		=> $user,
			'tasks'		=> $tasks,
			'events'	=> $events
		) );
		$this->logicMail->handleMail( $mail, $user, $language );
		return TRUE;
	}
}
?>
