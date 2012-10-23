<?php
class Job_Work_Mission extends Job_Abstract{

	/**
	 *	Archive old messages, sessions, users etc.
	 *	@access		public
	 *	@todo		implement this job
	 *	@return		void
	 */
	public function archive(){}

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
		$useSettings	= $this->env->getModules()->has( 'Manage_My_User_Setting' );
		$count			= 0;
		foreach( $modelUser->getAll( array( 'status' => '>0' ) ) as $user ){						//  get all active users
			if( !$user->email )																		//  no mail address configured for user
				continue;																			//  @todo	kriss: handle this exception state!
			if( $useSettings )
				$config	= Model_User_Setting::applyConfigStatic( $this->env, $user->userId );
			if( !$config->get( 'module.work_mission.mail.active' ) )
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
