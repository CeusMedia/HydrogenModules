<?php

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
	protected int $currentUserId;

	public function add(): void
	{
		if( $this->request->getMethod()->isPost() ){
			$userIds	= $this->request->get( 'userIds', [] );
			if( [] === $userIds ){
				$this->env->getMessenger()->noteError( 'No users selected' );
				$this->restart( 'add', TRUE );
			}
			$notificationMessageId	= $this->modelMessage->add( [
				'creatorId'				=> $this->currentUserId,
				'type'					=> (int) $this->request->get( 'type' ),
				'priority'				=> (int) $this->request->get( 'priority' ),
				'status'				=> (int) $this->request->get( 'status' ),
				'title'					=> $this->request->get( 'title' ),
				'content'				=> '',
				'link'					=> $this->request->get( 'link' ),
				'dateStart'				=> $this->request->get( 'dateStart' ),
				'dateEnd'				=> $this->request->get( 'dateEnd' ),
				'createdAt'				=> time(),
				'modifiedAt'			=> time(),
			] );
			$this->modelMessage->edit( $notificationMessageId, [
				'content'	=> $this->request->get( 'content' ),
			], FALSE );
			foreach( $userIds as $userId )
				$this->modelRecipient->add( [
					'notificationMessageId'	=> $notificationMessageId,
					'userId'				=> $userId,
					'status'				=> Model_Notification_Recipient::STATUS_NEW,
					'createdAt'				=> time(),
					'modifiedAt'			=> time(),
				] );
			$this->restart( 'edit/'.$notificationMessageId, TRUE );
		}
	}

	public function edit( int|string $notificationMessageId ): void
	{
		/** @var Entity_Notification_Message|NULL $message */
		$message	= $this->modelMessage->get( $notificationMessageId );
		if( NULL === $message ){
			$this->env->getMessenger()->noteError( 'No message found' );
			$this->restart( NULL, TRUE );
		}

		if( $this->request->getMethod()->isPost() ){
			$this->modelMessage->edit( $notificationMessageId, [
#				'type'			=> (int) $this->request->get( 'type' ),
#				'priority'		=> (int) $this->request->get( 'priority' ),
#				'status'		=> (int) $this->request->get( 'status' ),
				'title'			=> $this->request->get( 'title' ),
#				'link'			=> $this->request->get( 'link' ),
#				'dateStart'		=> $this->request->get( 'dateStart' ),
#				'dateEnd'		=> $this->request->get( 'dateEnd' ),
				'modifiedAt'	=> time(),
			] );
			$content	= $this->request->get( 'content' );
			if( $message->content !== $content )
				$this->modelMessage->edit( $notificationMessageId, ['content' => $content], FALSE );
			$this->restart( NULL, TRUE );
		}
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

	public function remove( int|string $notificationMessageId ): void
	{
		$this->modelRecipient->removeByIndex( 'notificationMessageId', $notificationMessageId );
		$this->modelMessage->remove( $notificationMessageId );
		$this->restart( NULL, TRUE );
	}

	public function view(): void
	{
		$notificationMessageId	= $this->session->get( 'work_notification_id' );
		$from					= $this->session->get( 'work_notification_from' );
		if( NULL === $notificationMessageId )
			$this->restart();
		if( $this->request->getMethod()->isPost() ){
			$confirmed		= (bool) $this->request->get( 'confirm', FALSE );
			if( $confirmed )
				$this->modelRecipient->editByIndices( [
					'notificationMessageId' => $notificationMessageId,
					'userId'				=> $this->currentUserId,
					'status'				=> Model_Notification_Recipient::STATUS_NEW,
				], [
					'status'		=> Model_Notification_Recipient::STATUS_SEEN,
					'modifiedAt'	=> time(),
				] );
			$this->restart( $from );
		}

		$this->addData( 'message', $this->modelMessage->get( $notificationMessageId ) );
		$this->addData( 'from', $from );
	}

	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->modelMessage		= new Model_Notification_Message( $this->env );
		$this->modelRecipient	= new Model_Notification_Recipient( $this->env );

		$this->currentUserId	= Logic_Authentication::getInstance( $this->env )->getCurrentUserId() ?? 0;
	}
}
