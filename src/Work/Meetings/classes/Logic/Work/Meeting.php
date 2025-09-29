<?php

class Logic_Work_Meeting extends CeusMedia\HydrogenFramework\Logic\Shared
{
	protected Logic_Authentication $logicAuth;
	protected Logic_Mail $logicMail;
	protected Logic_User $logicUser;
	protected Model_Work_Meeting $modelMeeting;
	protected Model_Work_Meeting_Participant $modelParticipant;

	public function getActiveMeetingsOfCurrentUser(): array
	{
		$conditions	= [
			'status'	=> Model_Work_Meeting::STATUS_ACTIVE,
		];
		$orders		= ['dateStart' => 'ASC'];
		$meetingIds	= $this->modelParticipant->getAllByIndices( [
			'meetingId'	=> $this->modelMeeting->getAll( $conditions, $orders ),
			'userId'	=> $this->logicAuth->getCurrentUserId(),
		], [], [], ['meetingId'] );

		$logicUser	= Logic_User::getInstance( $this->env );

		/** @var Entity_Work_Meeting $meetings */
		$meetings	= $this->modelMeeting->getAllByIndex( 'meetingId', $meetingIds );
		foreach( $meetings as $meeting ){
			$meeting->participants	= $this->modelParticipant->getAllByIndex( 'meetingId', $meeting->id );
			foreach( $meetings->participants as $participant )
				$participant->user	= $logicUser->getUser( $participant->userId );
		}
		return $meetings;
	}

	public function getMeeting( $meetingId ): ?Entity_Work_Meeting
	{
		/** @var ?Entity_Work_Meeting $meeting */
		$meeting	= $this->modelMeeting->get( $meetingId );
		if( NULL !== $meeting )
			$meeting->participants	= $this->modelParticipant->getAllByIndex( 'meetingId', $meetingId, ['type' => 'ASC'] );
		return $meeting;
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
		$currentUserId	= $this->logicAuth->getCurrentUserId();
		$language		= $this->env->getLanguage()->getLanguage();
		/** @var Entity_Work_Meeting_Participant[] $participants */
		$participants	= $this->modelParticipant->getAllByIndex( 'meetingId', $meeting->meetingId );
		foreach( $participants as $participant ){
			if( $participant->userId === $currentUserId )
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
		$currentUserId	= $this->logicAuth->getCurrentUserId();
		$language		= $this->env->getLanguage()->getLanguage();
		/** @var Entity_Work_Meeting_Participant[] $participants */
		$participants	= $this->modelParticipant->getAllByIndex( 'meetingId', $meeting->meetingId );
		foreach( $participants as $participant ){
			if( $participant->userId === $currentUserId )
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
		$language		= $this->env->getLanguage()->getLanguage();
		/** @var Entity_Work_Meeting_Participant[] $participants */
		$participants	= $this->modelParticipant->getAllByIndex( 'meetingId', $meeting->meetingId );
		foreach( $participants as $participant ){
			$user	= $this->logicUser->getUser( $participant->userId );
			$mail	= new Mail_Work_Meeting_Reminder( $this->env, [
				'meeting'	=> $meeting,
				'user'		=> $user,
			] );
			$this->logicMail->enqueueMail( $mail, $language, $user );
		}
		return count( $participants );
	}

	/**
	 *	@param		Entity_Work_Meeting		$meeting
	 *	@param		array					$updates
	 *	@return		int						Number of sent mails
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendMailsOnUpdate( Entity_Work_Meeting $meeting, array $updates = [] ): int
	{
		if( [] === $updates )
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
				'updates'	=> $updates,
			] );
			$this->logicMail->enqueueMail( $mail, $language, $user );
		}
		return count( $participants );
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
	}
}
