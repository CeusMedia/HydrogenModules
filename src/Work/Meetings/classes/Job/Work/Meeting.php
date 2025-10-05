<?php
class Job_Work_Meeting extends Job_Abstract
{
	public function close(): int
	{
		$model		= new Model_Work_Meeting( $this->env );
		/** @var Entity_Work_Meeting[] $meetings */
		$meetings	= $model->getAll( [
			'status'	=> Model_Work_Meeting::STATUS_ACTIVE,
			'dateEnd'	=> '<= '.date( 'Y-m-d H:i:s' ),
		] );
		foreach( $meetings as $meeting ){
			$model->edit( $meeting->meetingId, [
				'jobScheduleId'	=> 0,
				'status'		=> Model_Work_Meeting::STATUS_DONE,
				'modifiedAt'	=> time(),
			] );
			if( 0 !== $meeting->jobScheduleId ){
				$model	= Model_Job_Schedule::getInstance( $this->env );
				$model->remove( $meeting->jobScheduleId );
			}
		}
		return count( $meetings );
	}

	public function remind(): void
	{
		$model	= new Model_Work_Meeting( $this->env );

		$id		= (int) $this->parameters->get( 'id', 0 );
		if( 0 !== $id ){
			/** @var ?Entity_Work_Meeting $meeting */
			$meeting	= $model->get( $id );
			if( NULL === $meeting ){
				$this->out( 'Error: Invalid ID' );
				$this->logError( 'Error: Invalid ID' );
				return;
			}
			$this->out( 'Sending reminder for: '.$meeting->title );
			$this->sendMeetingReminder( $meeting );
		}
		else{
			$datetime	= new Datetime( 'now' );
			$target		= $datetime->add( new DateInterval( 'PT1H' ) );
			if( $this->verbose )
				$this->out( 'Target DateTime: '.$target->format( 'Y-m-d H:i' ).':00' );
			$conditions	= [
				'status'	=> Model_Work_Meeting::STATUS_ACTIVE,
				'dateStart'	=> $target->format('Y-m-d H:i' ).':00'
			];
			$meetings	= $model->getAll( $conditions );
			foreach ( $meetings as $meeting ){
				$this->out( 'Sending reminder for: '.$meeting->title );
				$this->sendMeetingReminder( $meeting );
			}
		}
	}

	protected function sendMeetingReminder( Entity_Work_Meeting $meeting ): int
	{
		$model		= new Model_Work_Meeting_Participant( $this->env );
		$logicMail	= Logic_Mail::getInstance( $this->env );
		$logicUser	= Logic_User::getInstance( $this->env );

		/** @var Entity_Work_Meeting_Participant[] $participants */
		$participants	= $model->getAllByIndices( [
			'meetingId'	=> $meeting->meetingId,
			'type'		=> '!= '.Model_Work_Meeting_Participant::TYPE_INFORMED,
		] );
		foreach( $participants as $participant ){
			$user	= $logicUser->getUser( $participant->userId );
			$mail		= new Mail_Work_Meeting_Reminder( $this->env, [
				'meeting'	=> $meeting,
				'user'		=> $user,
			] );
			$logicMail->handleMail( $mail, $user, $this->env->getLanguage()->getLanguage() );
		}
		return count( $participants );
	}
}
