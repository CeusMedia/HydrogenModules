<?php
class Controller_Work_Meeting extends \CeusMedia\HydrogenFramework\Controller
{
	protected Logic_Work_Meeting $logic;
	protected Logic_Authentication $logicAuth;
	protected Model_Work_Meeting $modelMeeting;
	protected Model_Work_Meeting_Participant $modelParticipant;

	public function add(): void
	{
		if( $this->env->getRequest()->getMethod()->isPost() ){
			$data		= $this->env->getRequest()->getAllFromSource( 'POST', TRUE );
			$entity		= new Entity_Work_Meeting();
			$entity->creatorId	= Logic_Authentication::getInstance( $this->env )->getCurrentUserId();
			$entity->dateStart	= $data->get( 'dateStart' );
			$entity->dateEnd	= $data->get( 'dateEnd' );
			$entity->location	= $data->get( 'location' );
			$entity->title		= $data->get( 'title' );
			$entity->content	= '';
			$entity->link		= $data->get( 'link' );
			$entity->createdAt	= time();
			$entity->modifiedAt	= time();

			$meetingId	= $this->modelMeeting->add( $entity, TRUE );
			$entity->content	= $data->get( 'content' );
			$this->modelMeeting->edit( $meetingId, $entity, FALSE );

			/** @var Entity_Work_Meeting $meeting */
			$meeting	= $this->modelMeeting->get( $meetingId );
			$this->logic->sendMailsOnCreated( $meeting );

			$this->restart( 'edit/'.$meetingId, TRUE );
		}
	}

	public function edit( int|string $meetingId ): void
	{
		/** @var ?Entity_Work_Meeting $meeting */
		$meeting	= $this->modelMeeting->get( $meetingId );
		if( NULL === $meeting ){
			$this->env->getMessenger()->noteError( 'Invalid meeting ID.' );
			$this->restart( NULL, TRUE );
		}
		if( $this->env->getRequest()->getMethod()->isPost() ){
			$data		= $this->env->getRequest()->getAllFromSource( 'POST', TRUE );
			$entity		= clone $meeting;
			$fields		= ['dateStart', 'dateEnd', 'location', 'title', 'content', 'link'];
			$changes	= [];
			$updates	= [];
			foreach( $fields as $field ){
				$value	= $data->get( $field, $meeting->get( $field ) );
				if( $meeting->get( $field ) !== $value ){
					$changes[$field]	= [$meeting->get( $field ), $value];
					$updates[$field]	= $value;
				}
			}
			if( [] !== $changes ){
				$this->modelMeeting->edit( $meetingId, $updates, FALSE );
				$this->logic->sendMailsOnUpdate( $meeting, $changes );
			}
			$this->restart( 'edit/'.$meetingId, TRUE );
		}

		$this->addData( 'meeting', $meeting );
	}

	public function index(): void
	{
		$conditions	= [];
		$orders		= [];
		$limits		= [];
		$meetings	= $this->modelMeeting->getAll( $conditions, $orders, $limits );
		$this->addData( 'meetings', $meetings );
	}

	public function setStatus( int|string $meetingId, int $status ): void
	{
		$meeting	= $this->modelMeeting->get( $meetingId );
		if( NULL === $meeting ){
			$this->env->getMessenger()->noteError( 'Invalid meeting ID' );
			$this->restart( NULL, TRUE );
		}

		$this->modelMeeting->edit( $meetingId, [
			'status'		=> $status,
			'modifiedAt'	=> time(),
		] );
		switch( $status ){
			case Model_Work_Meeting::STATUS_CANCELLED:
				if( Model_Work_Meeting::STATUS_ACTIVE === $meeting->status ){
					/** @var Entity_Work_Meeting $meeting */
					$meeting	= $this->modelMeeting->get( $meetingId );
					$this->logic->sendMailsOnCancelled( $meeting );
				}
				break;
			case Model_Work_Meeting::STATUS_ACTIVE:
				if( Model_Work_Meeting::STATUS_NEW === $meeting->status ){
					/** @var Entity_Work_Meeting $meeting */
					$meeting	= $this->modelMeeting->get( $meetingId );
					$this->logic->sendMailsOnCreated( $meeting );
				}
				break;
		}

		$this->restart( NULL, TRUE );
	}

	public function remove( int|string $meetingId ): void
	{
		/** @var ?Entity_Work_Meeting $meeting */
		$meeting	= $this->modelMeeting->get( $meetingId );
		if( NULL === $meeting ){
			$this->env->getMessenger()->noteError( 'Invalid meeting ID' );
			$this->restart( NULL, TRUE );
		}
		$allowedStatuses	= [
			Model_Work_Meeting::STATUS_CANCELLED,
			Model_Work_Meeting::STATUS_NEW,
			Model_Work_Meeting::STATUS_OUTDATED,
			Model_Work_Meeting::STATUS_DONE,
		];
		if( !in_array( $meeting->status, $allowedStatuses, TRUE ) )
			$this->restart( NULL, TRUE );

		$this->modelParticipant->removeByIndex( 'meetingId', $meetingId );
		$this->modelMeeting->remove( $meetingId );
		$this->restart( NULL, TRUE );
	}

	public function view( int|string|NULL $meetingId = NULL ): void
	{
		$meetings	= $this->logic->getActiveMeetingsOfCurrentUser();
		$this->addData( 'meetings', $meetings );

		if( 0 !== ( (int) trim( $meetingId ?? '' ) ) )
			$this->addData( 'meeting', $this->logic->getMeeting( $meetingId ) );
	}

	protected function __onInit(): void
	{
		$this->logic			= Logic_Work_Meeting::getInstance( $this->env );
		$this->logicAuth		= Logic_Authentication::getInstance( $this->env );
		$this->modelMeeting		= new Model_Work_Meeting( $this->env );
		$this->modelParticipant	= new Model_Work_Meeting_Participant( $this->env );
	}
}