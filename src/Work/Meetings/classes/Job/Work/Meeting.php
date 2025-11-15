<?php
class Job_Work_Meeting extends Job_Abstract
{
	protected Logic_Work_Meeting $logic;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function close(): void
	{
		$id		= (int) $this->parameters->get( 'id', 0 );
		$count	= 0;
		if( 0 !== $id ){
			/** @var ?Entity_Work_Meeting $meeting */
			$meeting	= $this->logic->getMeeting( $id );
			if( NULL === $meeting ){
				$this->out( 'Error: Invalid ID' );
				$this->logError( 'Error: Invalid ID' );
				return;
			}
			$this->out( 'Closing meeting: '.$meeting->title );
			$count	+= (int) $this->logic->closeMeeting( $meeting );
			$this->setResult( Entity_Job_Result::STATUS_SUCCESS, $count );
		}
		else{
			$meetings	= $this->logic->getClosableMeetings();
			foreach( $meetings as $meeting ){
				$this->out( 'Closing meeting: '.$meeting->title );
				$count	+= (int) $this->logic->closeMeeting( $meeting );
			}
		}
		$status	= Entity_Job_Result::STATUS_UNKNOWN;
		if( 0 !== $count )
			$status	= Entity_Job_Result::STATUS_SUCCESS;
		$this->setResult( $status, $count );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remind(): void
	{
		$id		= (int) $this->parameters->get( 'id', 0 );
		$count	= 0;
		if( 0 !== $id ){
			/** @var ?Entity_Work_Meeting $meeting */
			$meeting	= $this->logic->getMeeting( $id );
			if( NULL === $meeting ){
				$this->out( 'Error: Invalid ID' );
				$this->logError( 'Error: Invalid ID' );
				$this->setResult( Entity_Job_Result::STATUS_FAILURE, 0 );
				return;
			}
			$this->out( 'Sending reminder for: '.$meeting->title );
			$count	+= $this->logic->sendMailsOnReminder( $meeting );
		}
		else{
			if( $this->verbose ){
				$datetime	= new Datetime( 'now' );
				$target		= $datetime->add( new DateInterval( 'PT1H' ) );
				$this->out( 'Target DateTime: '.$target->format( 'Y-m-d H:i' ).':00' );
			}
			$meetings	= $this->logic->getRemindableMeetings();
			foreach ( $meetings as $meeting ){
				$this->out( 'Sending reminder for: '.$meeting->title );
				$count	+= $this->logic->sendMailsOnReminder( $meeting );
			}
		}

		$status	= Entity_Job_Result::STATUS_UNKNOWN;
		if( 0 !== $count )
			$status	= Entity_Job_Result::STATUS_SUCCESS;
		$this->setResult( $status, $count );
	}

	protected function __onInit(): void
	{
		$this->logic	= Logic_Work_Meeting::getInstance( $this->env );
	}
}
