<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use CeusMedia\Common\Net\HTTP\PartitionSession as HttpPartitionSession;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;

class Controller_Work_Notification extends Controller
{
	protected Model_Notification_Message $modelMessage;
	protected Model_Notification_Recipient $modelRecipient;
	protected HttpRequest $request;
	protected HttpPartitionSession $session;
	protected MessengerResource $messenger;
	protected int|string $currentUserId;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function add(): void
	{
		if( $this->request->getMethod()->isPost() )
			$this->handlePostOnAdd();

		/** @var Logic_User $logicUser */
		$logicUser	= $this->env->getLogic()->get( 'User' );
		$roles		= $logicUser->getRoles( ['access' => Model_Role::ACCESS_ACL], ['roleId' => 'DESC'] );
		foreach( $roles as $nr => $role ){
			if( !$this->env->getAcl()->hasRight( $role->roleId, 'work/notification', 'view' ) )
				unset( $roles[$nr] );
			else
				$role->nrUsers	= $logicUser->countRoleUsers( $role );
		}
		$this->addData( 'roles', $roles );
		$this->addData( 'inputTitle', $this->request->get( 'title', '' ) );
		$this->addData( 'inputDateStart', $this->request->get( 'dateStart', '' ) );
		$this->addData( 'inputDateEnd', $this->request->get( 'dateEnd', '' ) );
		$this->addData( 'inputContent', $this->request->get( 'content', '' ) );
		$this->addData( 'inputLink', $this->request->get( 'link', '' ) );
	}

	/**
	 *	@param		int|string		$messageId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function edit( int|string $messageId ): void
	{
		/** @var Entity_Notification_Message|NULL $message */
		$message	= $this->modelMessage->get( $messageId );
		if( NULL === $message ){
			$this->env->getMessenger()->noteError( 'No message found' );
			$this->restart( NULL, TRUE );
		}

		if( $this->request->getMethod()->isPost() ){
			$this->modelMessage->edit( $messageId, [
#				'type'			=> (int) $this->request->get( 'type' ),
#				'priority'		=> (int) $this->request->get( 'priority' ),
				'status'		=> (int) $this->request->get( 'status' ),
				'title'			=> $this->request->get( 'title' ),
				'link'			=> $this->request->get( 'link' ),
				'dateStart'		=> $this->request->get( 'dateStart' ),
				'dateEnd'		=> $this->request->get( 'dateEnd' ) ? $this->request->get( 'dateEnd' ) : NULL,
				'modifiedAt'	=> time(),
			] );
			$content	= $this->request->get( 'content' );
			if( $message->content !== $content )
				$this->modelMessage->edit( $messageId, ['content' => $content], FALSE );
			$this->restart( NULL, TRUE );
		}

		/** @var Entity_Notification_Recipient[] $recipientsSeen */
		$recipientsSeen	= $this->modelRecipient->getAll( [
			'notificationMessageId'	=> $messageId,
			'status'				=> Model_Notification_Recipient::STATUS_SEEN,
		] );
		/** @var Entity_Notification_Recipient[] $recipientsSeen */
		$recipientsNew	= $this->modelRecipient->getAll( [
			'notificationMessageId'	=> $messageId,
			'status'				=> Model_Notification_Recipient::STATUS_NEW,
		] );

		$usersSeen	= [];
		$usersNew	= [];

		/** @var Logic_User $logicUser */
		$logicUser	= $this->env->getLogic()->get( 'User' );

		$roles		= [];
		foreach( $logicUser->getRoles() as $role )
			$roles[$role->roleId]	= $role;

		foreach( $recipientsSeen as $recipient ){
			$user		= $logicUser->getUser( $recipient->userId );
			if( NULL === $user )
				continue;
			$usersSeen[$recipient->userId]	= (object) [
				'userId'	=> $recipient->userId,
				'seenAt'	=> $recipient->modifiedAt,
				'username'	=> $user->username,
				'firstname'	=> $user->firstname,
				'surname'	=> $user->surname,
				'email'		=> $user->email,
				'role'		=> $roles[$user->roleId],
			];
		}
		foreach( $recipientsNew as $recipient ){
			$user	= $logicUser->getUser( $recipient->userId );
			if( NULL === $user )
				continue;
			$usersNew[$recipient->userId]	= (object) [
				'userId'	=> $recipient->userId,
				'seenAt'	=> $recipient->modifiedAt,
				'username'	=> $user->username,
				'firstname'	=> $user->firstname,
				'surname'	=> $user->surname,
				'email'		=> $user->email,
				'role'		=> $roles[$user->roleId],
			];
		}

		$this->addData( 'recipientsSeen', $usersSeen );
		$this->addData( 'recipientsNew', $usersNew );
		$this->addData( 'message', $message );
	}

	public function index(): void
	{
		$conditions	= [];
		$limits		= [0, 10];
		$orders		= [];
		$messages	= $this->modelMessage->getAll( $conditions, $orders, $limits );
		foreach( $messages as $message ){
			$message->nrRecipients		= $this->modelRecipient->countByIndices( [
				'notificationMessageId' => $message->notificationMessageId
			] );
			$message->nrRecipientsSeen	= $this->modelRecipient->countByIndices( [
				'notificationMessageId' => $message->notificationMessageId,
				'status'				=> Model_Notification_Recipient::STATUS_SEEN,
			] );
		}
		$this->addData( 'messages', $messages );
	}

	/**
	 *	@param		int|string		$messageId
	 *	@return		void
	 */
	public function remove( int|string $messageId ): void
	{
		$this->modelRecipient->removeByIndex( 'notificationMessageId', $messageId );
		$this->modelMessage->remove( $messageId );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$messageId
	 *	@param		int				$status
	 *	@return		void
	 */
	public function setStatus( int|string $messageId, int $status ): void
	{
		$this->modelMessage->edit( $messageId, [
			'status'		=> $status,
			'modifiedAt'	=> time(),
		] );
		$this->restart( 'edit/'.$messageId, TRUE );
	}

	/**
	 *	@return		void
	 */
	public function view(): void
	{
		$messageId	= $this->session->get( 'work_notification_id' );
		$from		= $this->session->get( 'work_notification_from' );
		$words		= $this->env->getLanguage()->getWords( 'work/notification' );
		if( NULL === $messageId )
			$this->restart();
		if( $this->request->getMethod()->isPost() ){
			$confirmed		= (bool) $this->request->get( 'confirm', FALSE );
			if( $confirmed )
				$this->modelRecipient->editByIndices( [
					'notificationMessageId' => $messageId,
					'userId'				=> $this->currentUserId,
					'status'				=> Model_Notification_Recipient::STATUS_NEW,
				], [
					'status'		=> Model_Notification_Recipient::STATUS_SEEN,
					'modifiedAt'	=> time(),
				] );
			$nrUnseen	= $this->modelRecipient->countByIndices( [
				'notificationMessageId' => $messageId,
				'status'				=> Model_Notification_Recipient::STATUS_NEW,
			] );
			if( 0 === $nrUnseen ){
				$this->modelMessage->edit( $messageId, [
					'status'	=> Model_Notification_Message::STATUS_SEEN,
				] );
			}
			$this->restart( $from );
		}

		if( '' !== ( $words['hook']['message'] ?? '' ) )
			$this->env->getMessenger()->noteNotice( $words['hook']['message'] );
		$this->addData( 'message', $this->modelMessage->get( $messageId ) );
		$this->addData( 'from', $from );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->modelMessage		= new Model_Notification_Message( $this->env );
		$this->modelRecipient	= new Model_Notification_Recipient( $this->env );

		$this->currentUserId	= Logic_Authentication::getInstance( $this->env )->getCurrentUserId() ?? 0;
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function handlePostOnAdd(): void
	{
		$dbc		= $this->env->getDatabase();
		$words		= $this->env->getLanguage()->getWords( 'work/notification' );
		$roleIds	= $this->request->get( 'roleIds', [] );
		$message	= Entity_Notification_Message::fromArray( [
			'creatorId'				=> $this->currentUserId,
#			'type'					=> (int) $this->request->get( 'type' ),
#			'priority'				=> (int) $this->request->get( 'priority' ),
			'status'				=> (int) $this->request->get( 'status' ),
			'title'					=> $this->request->get( 'title' ),
			'content'				=> '',
			'link'					=> $this->request->get( 'link' ),
			'dateStart'				=> $this->request->get( 'dateStart' ),
			'dateEnd'				=> $this->request->get( 'dateEnd' ) ? $this->request->get( 'dateEnd' ) : NULL,
			'createdAt'				=> time(),
		] );

		if( [] === $roleIds ){
			$this->env->getMessenger()->noteError( $words['add']['msgMissingRoles'] );
			return;
		}

		$dbc->beginTransaction();
		try{
			$messageId	= $this->modelMessage->add( $message );
			$this->modelMessage->edit( $messageId, [
				'content'		=> $this->request->get( 'content' ),
				'modifiedAt'	=> time(),
			], FALSE );

			$logicUser	= $this->env->getLogic()->get( 'User' );
			foreach( $roleIds as $roleId ){
				/** @var Entity_User $user */
				foreach( $logicUser->getRoleUsers( $roleId ) as $user )
					$this->modelRecipient->add( [
						'notificationMessageId'	=> $messageId,
						'userId'				=> $user->userId,
						'status'				=> Model_Notification_Recipient::STATUS_NEW,
						'createdAt'				=> time(),
						'modifiedAt'			=> time(),
					] );

			}
			$dbc->commit();
			$this->restart( 'edit/'.$messageId, TRUE );
		}
		catch( Throwable $e ){
			$dbc->rollBack();
			$this->env->getLog()->logException( $e );
		}
	}
}
