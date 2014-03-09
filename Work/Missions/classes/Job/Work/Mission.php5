<?php
class Job_Work_Mission extends Job_Abstract{

	/**
	 *	Archive old messages, sessions, users etc.
	 *	@access		public
	 *	@todo		implement this job
	 *	@return		void
	 */
	public function archive(){}

    public function informAboutChanges(){
		$modelChange	= new Model_Mission_Change( $this->env );
		$modelMission	= new Model_Mission( $this->env );
		$modelUser		= new Model_User( $this->env );
		$useProjects	= $this->env->getModules()->has( 'Manage_Projects' );
		$changes		= $modelChange->getAll();
		$count			= 0;
		$config			= $this->env->getConfig();
		$useSettings	= $this->env->getModules()->has( 'Manage_My_User_Settings' );
		foreach( $changes as $change ){
			$mission	= $modelMission->get( $change->missionId );
			$receivers	= array();
			if( $useProjects ){
				$modelProject	= new Model_Project( $this->env );
				$receivers		= $modelProject->getProjectUsers( (int) $mission->projectId );
			}
			$receivers[$mission->workerId] = $modelUser->get( $mission->workerId );
			foreach( $receivers as $receiverId => $receiver ){
#				if( $receiver->email !== "kriss@ceusmedia.de" )
#					continue;
				if( 1 || (int) $receiver->userId !== (int) $change->userId ){
					if( $useSettings )
						$config	= Model_User_Setting::applyConfigStatic( $this->env, $receiver->userId );
					if( !$config->get( 'module.work_missions.mail.active' ) )
						continue;
					if( !$config->get( 'module.work_missions.mail.changes' ) )
						continue;
					switch( strtolower( $change->type ) ){
						case 'update':
							$mail   = new Mail_Work_Mission_Update( $this->env, array(
								'missionBefore'	=> unserialize( $change->data ),
								'missionAfter'	=> $mission,
								'user'			=> $receiver
							) );
							$mail->sendTo( $receiver );
							$count++;
							break;
						case 'new':
							$mail	= new Mail_Work_Mission_New( $this->env, array(
								'mission'	=> $mission,
								'user'		=> $receiver
							) );
							$mail->sendTo( $receiver );
							$count++;
							break;
					}

				}
			}
//			$modelChange->remove( $change->missionChangeId );
		}
		$this->out( 'Sent '.$count.' mails.' );
		return $count;
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
		$modelUser		= new Model_User( $this->env );
		$modelMission	= new Model_Mission( $this->env );
		$config			= $this->env->getConfig();
		$useSettings	= $this->env->getModules()->has( 'Manage_My_User_Settings' );
		$count			= 0;
		foreach( $modelUser->getAll( array( 'status' => '>0' ) ) as $user ){						//  get all active users
#			if( $user->email !== "kriss@ceusmedia.de" )
#				continue;
			if( !$user->email )																		//  no mail address configured for user
				continue;																			//  @todo	kriss: handle this exception state!
			if( $useSettings )
				$config	= Model_User_Setting::applyConfigStatic( $this->env, $user->userId );
			$config	= $config->getAll( 'module.work_missions.mail.', TRUE );
			if( !$config->get( 'active' ) || !$config->get( 'daily' ) )
				continue;
			if( (int) $config->get( 'daily.hour' ) != (int) date( "H" ) )
				continue;

			$groupings	= array( 'missionId' );														//  group by mission ID to apply HAVING clause
			$havings	= array(																	//  apply filters after grouping
				'ownerId = '.(int) $user->userId,													//  
				'workerId = '.(int) $user->userId,													//  
			);
			if( $this->env->getModules()->has( 'Manage_Projects' ) ){								//  look for module
				$modelProject	= new Model_Project( $this->env );									//  
				$userProjects	= $modelProject->getUserProjects( $user->userId );					//  get projects assigned to user
				if( $userProjects )																	//  projects found
					$havings[]	= 'projectId IN ('.join( ',', array_keys( $userProjects ) ).')';	//  add to HAVING clause
			}
			$havings	= array( join( ' OR ', $havings ) );										//	render HAVING clause

			//  --  TASKS  --  //
			$filters	= array(																	//  task filters
				'type'		=> 0,																	//  tasks only
				'status'	=> array( 0, 1, 2, 3 ),													//  states: new, accepted, progressing, ready
				'dayStart'	=> "<=".date( "Y-m-d", time() ),										//  present and past (overdue)
			);
			$order	= array( 'priority' => 'ASC' );
			$tasks	= $modelMission->getAll( $filters, $order, NULL, NULL, $groupings, $havings );	//  get filtered tasks ordered by priority

			//  --  EVENTS  --  //
			$filters	= array(																	//  event filters
				'type'		=> 1,																	//  events only
				'status'	=> array( 0, 1, 2, 3 ),													//  states: new, accepted, progressing, ready
				'dayStart'	=> "<=".date( "Y-m-d", time() ),										//  starting today
			);
			$order	= array( 'timeStart' => 'ASC' );
			$events	= $modelMission->getAll( $filters, $order, NULL, NULL, $groupings, $havings );	//  get filtered events ordered by start time

			if( !$events && !$tasks )																//  user has neither tasks nor events
				continue;																			//  do not send a mail, leave user alone

			$data	= array( 'user' => $user, 'tasks' => $tasks, 'events' => $events );				//  data for mail upcoming object
			$mail	= new Mail_Work_Mission_Daily( $this->env, $data );								//  create mail and populate data
			$mail->sendTo( $user );																	//  send mail to user
			$count++;
		}
		$this->out( 'Sent '.$count.' mails.' );
	}
}
?>
