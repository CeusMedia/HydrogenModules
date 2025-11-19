<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Meeting extends Controller
{
	protected Logic_Work_Meeting $logic;
	protected Logic_Authentication $logicAuth;
	protected Model_Work_Meeting $modelMeeting;
	protected Model_Work_Meeting_Participant $modelParticipant;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function add(): void
	{
		if( $this->env->getRequest()->getMethod()->isPost() ){
			/** @var Dictionary $data */
			$data			= $this->env->getRequest()->getAllFromSource( 'POST', TRUE );
			$currentUserId	= Logic_Authentication::getInstance( $this->env )->getCurrentUserId();
			$entity			= new Entity_Work_Meeting();
			$entity->creatorId	= $currentUserId;
			$entity->dateStart	= $data->get( 'dateStart_date' ).' '.$data->get( 'dateStart_time' );
			$entity->dateEnd	= $data->get( 'dateEnd_date' ).' '.$data->get( 'dateEnd_time' );
			$entity->location	= $data->get( 'location' );
			$entity->title		= $data->get( 'title' );
			$entity->content	= '';
			$entity->link		= $data->get( 'link' );
			$entity->createdAt	= time();
			$entity->modifiedAt	= time();

			$meetingId	= $this->modelMeeting->add( $entity );
			/** @var Entity_Work_Meeting $meeting */
			$meeting	= $this->modelMeeting->get( $meetingId );
			$entity->content	= $data->get( 'content' );
			$this->modelMeeting->edit( $meetingId, $entity, FALSE );

			$role	= $data->get( 'role', Model_Work_Meeting_Participant::TYPE_UNSPECIFIED );
			$entity	= new Entity_Work_Meeting_Participant();
			$entity->meetingId	= $meetingId;
			$entity->type		= $role;
			$entity->timestamp	= time();
			$entity->userId		= $currentUserId;
			$this->modelParticipant->add( $entity );
			$this->restart( 'edit/'.$meetingId, TRUE );
		}

		/** @var Dictionary $meeting */
		$meeting	= $this->env->getRequest()->getAll( NULL, TRUE );
		$this->addData( 'meeting', $meeting );
	}

	/**
	 *	@param		int|string		$meetingId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function addParticipants( int|string $meetingId ): void
	{
		/** @var Entity_Work_Meeting $meeting */
		$meeting	= $this->checkMeeting( $meetingId );
		$isEditable	= Model_Work_Meeting::STATUS_NEW === $meeting->status;
		$isPost		= $this->env->getRequest()->getMethod()->isPost();
		if( $isEditable && $isPost ){
			$logicUser	= Logic_User::getInstance( $this->env );
			/** @var Dictionary $data */
			$data		= $this->env->getRequest()->getAll( NULL, TRUE );
			$userIds	= [];
			switch( $data->get( 'source' ) ){
				case 'roles':
					foreach( $data->get( 'roleIds', [] ) as $roleId )
						foreach( $logicUser->getRoleUsers( $roleId ) as $user )
							$userIds[]	= $user->userId;
					break;
				case 'groups':
					foreach( $data->get( 'groupIds', [] ) as $groupId )
						foreach( $logicUser->getGroupUsers( $groupId ) as $user )
							$userIds[]	= $user->userId;
					break;
				case 'users':
					foreach( $data->get( 'userIds', [] ) as $userId )
						$userIds[]	= $userId;
					break;
			}
			$participantIds	= $this->modelParticipant->getAllByIndex( 'meetingId', $meetingId, [], [], ['userId'] );
			if( [] !== $userIds ){
				foreach( $userIds as $userId ){
					if( in_array( $userId, $participantIds ) )
						continue;
					$this->modelParticipant->add( [
						'meetingId'	=> $meetingId,
						'userId'	=> $userId,
						'type'		=> $data->get( 'type' ),
						'timestamp'	=> time(),
					] );
				}
			}
		}
		$this->restart( 'edit/'.$meetingId, TRUE );
	}

	/**
	 *	@param		int|string		$meetingId
	 *	@param		int|string		$userId
	 *	@return		void
	 */
	public function removeParticipant( int|string $meetingId, int|string $userId ): void
	{
		/** @var Entity_Work_Meeting $meeting */
		$meeting	= $this->checkMeeting( $meetingId );
		$isEditable	= Model_Work_Meeting::STATUS_NEW === $meeting->status;
		if( $isEditable ){
			$this->modelParticipant->removeByIndices( [
				'meetingId'	=> $meetingId,
				'userId'	=> $userId,
			] );
		}
		$this->restart( 'edit/'.$meetingId, TRUE );
	}

	/**
	 *	@param		int|string		$meetingId
	 *	@param		bool			$editMode
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		DateInvalidOperationException
	 */
	public function edit( int|string $meetingId, bool $editMode = FALSE ): void
	{
		/** @var Entity_Work_Meeting $meeting */
		$meeting	= $this->checkMeeting( $meetingId );
		$logicUser	= Logic_User::getInstance( $this->env );
		if( $this->env->getRequest()->getMethod()->isPost() ){
			/** @var Dictionary $data */
			$data		= $this->env->getRequest()->getAllFromSource( 'POST', TRUE );
			$data->set( 'dateStart', $data->get( 'dateStart_date' ).' '.$data->get( 'dateStart_time' ) );
			$data->set( 'dateEnd', $data->get( 'dateEnd_date' ).' '.$data->get( 'dateEnd_time' ) );
			$fields		= ['dateStart', 'dateEnd', 'location', 'title', 'content', 'link'];
			$changes	= [];
			$updates	= [];
			$entity		= clone $meeting;
			foreach( $fields as $field ){
				$value	= $data->get( $field, $meeting->get( $field ) );
				if( $meeting->get( $field ) !== $value ){
					$changes[$field]	= [$meeting->get( $field ), $value];
					$updates[$field]	= $value;
				}
			}
			if( [] !== $changes ){
				$this->modelMeeting->edit( $meetingId, $updates, FALSE );
				if( Model_Work_Meeting::STATUS_ACTIVE === $meeting->status ){
					$this->logic->sendMailsOnUpdate( $meeting, $changes );
					$this->logic->setJobSchedule( $this->logic->getMeeting( $meetingId ) );
				}
			}
			$this->restart( 'edit/'.$meetingId, TRUE );
		}
		$this->logic->extendMeetingsParticipantsByUser( $meeting );
		$this->addData( 'editMode', $editMode );
		$this->addData( 'meeting', $meeting );

		$roles		= $logicUser->getRoles( ['access' => Model_Role::ACCESS_ACL], ['roleId' => 'DESC'] );
		foreach( $roles as $nr => $role ){
			if( 0 && !$this->env->getAcl()->hasRight( $role->roleId, 'work/meeting', 'view' ) )
				unset( $roles[$nr] );
			else
				$role->nrUsers	= $logicUser->countRoleUsers( $role );
		}
		$this->addData( 'roles', $roles );

		$groups	= $logicUser->getGroups();
		$this->addData( 'groups', $groups );

		$users	= Model_User::getInstance( $this->env )->getAllByIndices( [
			'status'	=> Model_User::STATUS_ACTIVE,
		], ['username' => 'ASC'] );
		$this->addData( 'users', $users );
	}

	public function index(): void
	{
		$conditions	= [];
		$orders		= [];
		$limits		= [];
		$meetings	= $this->modelMeeting->getAll( $conditions, $orders, $limits );
		$this->addData( 'meetings', $meetings );
	}

	public function reuse( int|string $meetingId ): void
	{
		/** @var Entity_Work_Meeting $meeting */
		$meeting	= $this->checkMeeting( $meetingId );
		if( in_array( $meeting->status, [Model_Work_Meeting::STATUS_CANCELLED, Model_Work_Meeting::STATUS_DONE] ) )
			$this->modelMeeting->edit( $meetingId, [
				'status'		=> Model_Work_Meeting::STATUS_NEW,
				'modifiedAt'	=> time(),
			] );
		$this->restart( 'edit/'.$meetingId, TRUE );
	}

	/**
	 *	@param		int|string		$meetingId
	 *	@param		int				$status
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		DateInvalidOperationException
	 */
	public function setStatus( int|string $meetingId, int $status ): void
	{
		/** @var Entity_Work_Meeting $meeting */
		$meeting	= $this->checkMeeting( $meetingId );

		$this->modelMeeting->edit( $meetingId, [
			'status'		=> $status,
			'modifiedAt'	=> time(),
		] );
		switch( $status ){
			case Model_Work_Meeting::STATUS_CANCELLED:
				if( Model_Work_Meeting::STATUS_ACTIVE === $meeting->status ){
					/** @var Entity_Work_Meeting $meeting */
					$meeting	= $this->modelMeeting->get( $meetingId );
					$this->logic->unsetJobSchedule( $meeting );
					$this->logic->sendMailsOnCancelled( $meeting );
				}
				break;
			case Model_Work_Meeting::STATUS_ACTIVE:
				if( Model_Work_Meeting::STATUS_NEW === $meeting->status ){
					/** @var Entity_Work_Meeting $meeting */
					$meeting	= $this->modelMeeting->get( $meetingId );
					$this->logic->setJobSchedule( $meeting );
					$this->logic->sendMailsOnCreated( $meeting );
				}
				break;
		}

		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$meetingId
	 *	@return		void
	 */
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

	/**
	 *	@param		int|string|NULL		$meetingId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function view( int|string|NULL $meetingId = NULL ): void
	{
		$meetings	= $this->logic->getActiveMeetingsOfCurrentUser();
		$this->addData( 'meetings', $meetings );

		if( 0 !== ( (int) trim( $meetingId ?? '' ) ) ){
			$this->addData( 'meetingId', $meetingId );
			/** @var ?Entity_Work_Meeting $meeting */
			$meeting	= $this->logic->getMeeting( $meetingId );
			if( NULL === $meeting ){
				$this->env->getMessenger()->noteError( 'Das verlinkte Meeting ist vorbei und der Eintrag existiert nicht mehr.' );
				$this->addData( 'gone', TRUE );
			}
			else{
				$this->addData( 'meeting', $meeting );
				if( $this->logic->isActive( $meeting ) ){
					$script	= 'jQuery("#trigger-meeting-'.$meeting->meetingId.'").trigger("click")';
					$this->env->getPage()->js->addScriptOnReady( $script );
				}
				else{
					if( Model_Work_Meeting::STATUS_CANCELLED === $meeting->status ){
						$this->logic->extendMeetingsParticipantsByUser( $meeting );
						$this->addData( 'meeting', $meeting );
						$this->addData( 'meetingId', $meetingId );
						$script	= 'jQuery("#meeting-'.$meetingId.'").modal("show")';
						$this->env->getPage()->js->addScriptOnReady( $script );
					}
					else{
						$this->env->getMessenger()->noteError( 'Das verlinkte Meeting ist vorbei.' );
						$this->addData( 'gone', TRUE );
					}
				}
			}
		}

		$currentUserId	= Logic_Authentication::getInstance( $this->env )->getCurrentUserId();
		$this->addData( 'currentUserId', $currentUserId );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logic			= Logic_Work_Meeting::getInstance( $this->env );
		$this->logicAuth		= Logic_Authentication::getInstance( $this->env );
		$this->modelMeeting		= new Model_Work_Meeting( $this->env );
		$this->modelParticipant	= new Model_Work_Meeting_Participant( $this->env );
		$this->moduleConfig		= $this->env->getModules()->get( 'Work_Meetings' )->getConfigAsDictionary();
		$this->addData( 'moduleConfig', $this->moduleConfig );
	}

	/**
	 *	@param		int|string		$meetingId
	 *	@param		bool			$strict
	 *	@return		Entity_Work_Meeting|NULL
	 */
	protected function checkMeeting( int|string $meetingId, bool $strict = TRUE ): ?Entity_Work_Meeting
	{
		/** @var ?Entity_Work_Meeting $meeting */
		$meeting	= $this->modelMeeting->get( $meetingId );
		if( NULL === $meeting ){
			$this->env->getMessenger()->noteError( 'Invalid meeting ID.' );
			if( $strict )
				$this->restart( NULL, TRUE );
			return NULL;
		}
		return $meeting;
	}
}
