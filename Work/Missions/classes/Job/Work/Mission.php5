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

		$count	= 0;
		foreach( $modelUser->getAll() as $user ){
//			IF USER HAS CONFIGURED TO RECEIVE THIS TYPE OF MAILS									//  @todo	kriss: implement mail configuration

			if( $user->username != "kriss" ) continue;												//  @todo	kriss: remove on release

			if( !$user->email )
				continue;																			//  @todo	kriss: handle this exception state!

			$conditions	= array(																	//  task filters
				'type'		=> 0,																	//  tasks only
				'ownerId'	=> $user->userId,														//  users tasks only
				'status'	=> array( 0, 1, 2, 3 ),													//  states: new, accepted, progressing, ready
				'dayStart'	=> "<=".date( "Y-m-d", time() ),										//  present and past (overdue)
			);
			$tasks	= $modelMission->getAll( $conditions, array( 'priority' => 'ASC' ) );			//  get filtered tasks ordered by priority

			$conditions	= array(																	//  event filters
				'type'		=> 1,																	//  events only
				'ownerId'	=> $user->userId,														//  users events only
				'status'	=> array( 0, 1, 2, 3 ),													//  states: new, accepted, progressing, ready
				'dayStart'	=> date( "Y-m-d", time() ),												//  starting today
			);
			$events	= $modelMission->getAll( $conditions, array( 'timeStart' => 'ASC' ) );			//  get filtered events ordered by start time
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
