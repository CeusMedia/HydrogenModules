<?php

class Logic_Work_Meeting extends CeusMedia\HydrogenFramework\Logic\Shared
{
	protected Logic_Authentication $logicAuth;
	protected Logic_Mail $logicMail;
	protected Logic_User $logicUser;
	protected Model_Work_Meeting $modelMeeting;
	protected Model_Work_Meeting_Participant $modelParticipant;
	protected Model_Job_Schedule $modelSchedule;
	protected int|string $currentUserId				= 0;

	/**
	 *	@param		Entity_Work_Meeting		$meeting
	 *	@return		bool
	 */
	public function closeMeeting( Entity_Work_Meeting $meeting ): bool
	{
		if( Model_Work_Meeting::STATUS_ACTIVE !== $meeting->status )
			return FALSE;

		$this->modelMeeting->edit( $meeting->meetingId, [
			'jobScheduleIdRemind'	=> 0,
			'jobScheduleIdClose'	=> 0,
			'status'				=> Model_Work_Meeting::STATUS_DONE,
			'modifiedAt'			=> time(),
		] );
		if( 0 !== $meeting->jobScheduleIdRemind )
			$this->modelSchedule->remove( $meeting->jobScheduleIdRemind );
		if( 0 !== $meeting->jobScheduleIdClose )
			$this->modelSchedule->remove( $meeting->jobScheduleIdClose );

		return TRUE;
	}

	/**
	 *	@return		array<int|string,Entity_Work_Meeting>
	 *	@throws		ReflectionException
	 */
	public function getActiveMeetingsOfCurrentUser(): array
	{
		$conditions	= [
			'status'	=> Model_Work_Meeting::STATUS_ACTIVE,
//			'dateStart'	=> '> '.DateTime::...->sub(new DateInterval("PT1H")->format( 'Y-m-d H:i:s' ),
			'dateEnd'	=> '> '.date( 'Y-m-d H:i:s' ),
		];
		$orders		= ['dateStart' => 'ASC'];
		$activeMeetingIds	= $this->modelMeeting->getAll( $conditions, $orders, [], ['meetingId'] );
		if( [] === $activeMeetingIds )
			return [];

		$myMeetingIds	= $this->modelParticipant->getAllByIndices( [
			'meetingId'	=> $activeMeetingIds,
			'userId'	=> $this->currentUserId,
		], [], [], ['meetingId'] );
		if( [] === $myMeetingIds )
			return [];

		$logicUser	= Logic_User::getInstance( $this->env );

		/** @var Entity_Work_Meeting[] $meetings */
		$meetings	= $this->modelMeeting->getAllByIndex( 'meetingId', $myMeetingIds );
		$list		= [];
		foreach( $meetings as $meeting ){
			$this->extendMeetingsParticipantsByUser( $meeting );
			$list[$meeting->meetingId]	= $meeting;
		}
		return $list;
	}

	/**
	 *	@param		Entity_Work_Meeting		$meeting
	 *	@return		void
	 */
	public function extendMeetingsParticipantsByUser( Entity_Work_Meeting $meeting ): void
	{
		$logicUser	= Logic_User::getInstance( $this->env );

		if( [] === $meeting->participants )
			$meeting->participants	= $this->modelParticipant->getAllByIndex( 'meetingId', $meeting->meetingId );

		/** @var Entity_Work_Meeting_Participant $participant */
		foreach( $meeting->participants as $participant )
			$participant->user	= $logicUser->getUser( $participant->userId );
	}

	/**
	 *	@return		array<Entity_Work_Meeting>
	 */
	public function getClosableMeetings(): array
	{
		/** @var Entity_Work_Meeting[] $meetings */
		return $this->modelMeeting->getAll( [
			'status'	=> Model_Work_Meeting::STATUS_ACTIVE,
			'dateEnd'	=> '<= '.date( 'Y-m-d H:i:s' ),
		] );
	}

	/**
	 *	@param		int|string		$meetingId
	 *	@return		?Entity_Work_Meeting
	 */
	public function getMeeting( int|string $meetingId ): ?Entity_Work_Meeting
	{
		/** @var ?Entity_Work_Meeting $meeting */
		$meeting	= $this->modelMeeting->get( $meetingId );
		if( NULL !== $meeting )
			$this->extendMeetingsParticipantsByUser( $meeting );
		return $meeting;
	}

	/**
	 *	@return		array<Entity_Work_Meeting>
	 */
	public function getRemindableMeetings(): array
	{
		$datetime	= new Datetime( 'now' );
		$target		= $datetime->add( new DateInterval( 'PT1H' ) );
		$conditions	= [
			'status'	=> Model_Work_Meeting::STATUS_ACTIVE,
			'dateStart'	=> $target->format('Y-m-d H:i' ).':00'
		];
		return $this->modelMeeting->getAll( $conditions );
	}

	/**
	 *	Indicates whether a meeting is active by checking status and end date.
	 *	Returns TRUE if status is active and end date is in the future.
	 *	@param		Entity_Work_Meeting		$meeting
	 *	@return		bool
	 */
	public function isActive( Entity_Work_Meeting $meeting ): bool
	{
		if( Model_Work_Meeting::STATUS_ACTIVE !== $meeting->status )
			return FALSE;
		if( time() > strtotime( $meeting->dateEnd ) )
			return FALSE;
		return TRUE;
	}

	/**
	 *	@param		Entity_Work_Meeting		$meeting
	 *	@return		int						Number of sent mails
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendMailsOnCancelled( Entity_Work_Meeting $meeting ): int
	{
		$logicMail		= Logic_Mail::getInstance( $this->env );
		$language		= $this->env->getLanguage()->getLanguage();
		/** @var Entity_Work_Meeting_Participant[] $participants */
		$participants	= $this->modelParticipant->getAllByIndex( 'meetingId', $meeting->meetingId );
		foreach( $participants as $participant ){
			if( $participant->userId === $this->currentUserId )
				continue;
			$user	= $this->logicUser->getUser( $participant->userId );

			$mail	= new Mail_Work_Meeting_Cancelled( $this->env, [
				'meeting'	=> $meeting,
				'user'		=> $user,
			] );
			$logicMail->enqueueMail( $mail, $language, $user );
		}
		return count( $participants );
	}

	/**
	 *	@param		Entity_Work_Meeting		$meeting
	 *	@return		int						Number of sent mails
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendMailsOnCreated( Entity_Work_Meeting $meeting ): int
	{
		$language		= $this->env->getLanguage()->getLanguage();
		/** @var Entity_Work_Meeting_Participant[] $participants */
		$participants	= $this->modelParticipant->getAllByIndex( 'meetingId', $meeting->meetingId );
		foreach( $participants as $participant ){
			if( $participant->userId === $this->currentUserId )
				continue;
			$user	= $this->logicUser->getUser( $participant->userId );

			$mail	= new Mail_Work_Meeting_Created( $this->env, [
				'meeting'	=> $meeting,
				'user'		=> $user,
			] );
			$this->logicMail->enqueueMail( $mail, $language, $user );
		}
		return count( $participants );
	}

	/**
	 *	@param		Entity_Work_Meeting		$meeting
	 *	@return		int						Number of sent mails
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendMailsOnReminder( Entity_Work_Meeting $meeting ): int
	{
		$language	= $this->env->getLanguage()->getLanguage();
		/** @var Entity_Work_Meeting_Participant[] $participants */
		$participants	= $this->modelParticipant->getAllByIndices( [
			'meetingId'	=> $meeting->meetingId,
			'type'		=> '!= '.Model_Work_Meeting_Participant::TYPE_INFORMED,
		] );
		foreach( $participants as $participant ){
			$user	= $this->logicUser->getUser( $participant->userId );
			$mail	= new Mail_Work_Meeting_Reminder( $this->env, [
				'meeting'	=> $meeting,
				'user'		=> $user,
			] );
			$this->logicMail->handleMail( $mail, $user, $language );
		}
		return count( $participants );
	}

	/**
	 *	@param		Entity_Work_Meeting		$meeting
	 *	@param		array					$changes
	 *	@return		int						Number of sent mails
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendMailsOnUpdate( Entity_Work_Meeting $meeting, array $changes = [] ): int
	{
		if( [] === $changes )
			return 0;
		if( Model_Work_Meeting::STATUS_ACTIVE !== $meeting->status )
			return 0;

		$language		= $this->env->getLanguage()->getLanguage();
		$participants	= $this->modelParticipant->getAllByIndex( 'meetingId', $meeting->meetingId );
		foreach( $participants as $participant ){
			$user	= $this->logicUser->getUser( $participant->userId );
			$mail	= new Mail_Work_Meeting_Updated( $this->env, [
				'meeting'	=> $meeting,
				'user'		=> $user,
				'changes'	=> $changes,
			] );
			$this->logicMail->enqueueMail( $mail, $language, $user );
		}
		return count( $participants );
	}

	/**
	 *	Sets another user to be in focus.
	 *	Only needed to override the current session based user.
	 *	@param		int|string		$currentUserId
	 *	@return		self
	 */
	public function setCurrentUserId( int|string $currentUserId ): self
	{
		$this->currentUserId	= $currentUserId;
		return $this;
	}

	/**
	 *	@param		Entity_Work_Meeting		$meeting
	 *	@return		void
	 *	@throws		DateInvalidOperationException
	 *	@throws		ReflectionException
	 */
	public function setJobSchedule( Entity_Work_Meeting $meeting ): void
	{
		$reminderDate		= Datetime::createFromFormat( 'Y-m-d H:i:s', $meeting->dateStart )
			->sub( new DateInterval( "PT1H" ) );
		$closeDate		= Datetime::createFromFormat( 'Y-m-d H:i:s', $meeting->dateEnd );

		if( 0 !== $meeting->jobScheduleIdRemind ){
			$this->modelSchedule->edit( $meeting->jobScheduleIdRemind, [
				'expression'	=> $reminderDate->format( 'Y-m-d H:i' ),
				'modifiedAt'	=> time(),
			] );
			$this->modelSchedule->edit( $meeting->jobScheduleIdClose, [
				'expression'	=> $closeDate->format( 'Y-m-d H:i' ),
				'modifiedAt'	=> time(),
			] );
		}
		else{
			$modelJobDefinition	= new Model_Job_Definition( $this->env );

			$jobDefinitionId	= $modelJobDefinition->getByIndex( 'identifier', 'Work.Meeting.remind', [], ['jobDefinitionId'] );
			$jobScheduleIdRemind	= $this->modelSchedule->add( Entity_Job_Schedule::fromArray( [
				'jobDefinitionId'	=> $jobDefinitionId,
				'title'				=> 'Meeting Reminder #'.$meeting->meetingId,
				'type'				=> Model_Job_Schedule::TYPE_DATETIME,
				'status'			=> Model_Job_Schedule::STATUS_ENABLED,
				'reportMode'		=> Model_Job_Schedule::REPORT_MODE_NEVER,
				'reportChannel'		=> Model_Job_Schedule::REPORT_CHANNEL_NONE,
				'expression'		=> $reminderDate->format( 'Y-m-d H:i' ),
				'arguments'			=> "['id' => ".$meeting->meetingId."]",
				'createdAt'			=> time(),
			] ) );
			$this->modelMeeting->edit( $meeting->meetingId, [
				'jobScheduleIdRemind'	=> $jobScheduleIdRemind,
				'modifiedAt'			=> time(),
			] );

			$jobDefinitionId	= $modelJobDefinition->getByIndex( 'identifier', 'Work.Meeting.close', [], ['jobDefinitionId'] );
			$jobScheduleIdRemind	= $this->modelSchedule->add( Entity_Job_Schedule::fromArray( [
				'jobDefinitionId'	=> $jobDefinitionId,
				'title'				=> 'Meeting Closer #'.$meeting->meetingId,
				'type'				=> Model_Job_Schedule::TYPE_DATETIME,
				'status'			=> Model_Job_Schedule::STATUS_ENABLED,
				'reportMode'		=> Model_Job_Schedule::REPORT_MODE_NEVER,
				'reportChannel'		=> Model_Job_Schedule::REPORT_CHANNEL_NONE,
				'expression'		=> $closeDate->format( 'Y-m-d H:i' ),
				'arguments'			=> "['id' => ".$meeting->meetingId."]",
				'createdAt'			=> time(),
			] ) );
			$this->modelMeeting->edit( $meeting->meetingId, [
				'jobScheduleIdClose'	=> $jobScheduleIdRemind,
				'modifiedAt'			=> time(),
			] );
		}
	}

	/**
	 *	@param		Entity_Work_Meeting		$meeting
	 *	@return		void
	 */
	public function unsetJobSchedule( Entity_Work_Meeting $meeting ): void
	{
		if( 0 === $meeting->jobScheduleIdRemind )
			return;
		$this->modelSchedule->remove( $meeting->jobScheduleIdRemind );
		$this->modelSchedule->remove( $meeting->jobScheduleIdClose );
		$this->modelMeeting->edit( $meeting->meetingId, [
			'jobScheduleIdRemind'	=> 0,
			'jobScheduleIdClose'	=> 0,
			'modifiedAt'			=> time(),
		] );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logicAuth		= Logic_Authentication::getInstance( $this->env );
		$this->logicMail		= Logic_Mail::getInstance( $this->env );
		$this->logicUser		= Logic_User::getInstance( $this->env );
		$this->modelMeeting		= new Model_Work_Meeting( $this->env );
		$this->modelParticipant	= new Model_Work_Meeting_Participant( $this->env );
		$this->modelSchedule	= new Model_Job_Schedule( $this->env );
		$this->currentUserId	= $this->logicAuth->getCurrentUserId( FALSE ) ?? '';
	}
}
