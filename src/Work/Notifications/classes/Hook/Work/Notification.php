<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Notification extends Hook
{
	public function onAppControl(): bool
	{
		$auth			= Logic_Authentication::getInstance( $this->env );
		$currentUserId	= $auth->getCurrentUserId();
		if( !$auth->isAuthenticated() )
			return FALSE;

		if( 'work/notification/view' === $this->env->getRequest()->getPath() )
			return FALSE;

		$modelMessage	= new Model_Notification_Message( $this->env );
		$modelRecipient	= new Model_Notification_Recipient( $this->env );

		/** @var Entity_Notification_Message[] $messages */
		$messages	= $modelMessage->getAllByIndices( [
			'status'	=> Model_Notification_Message::STATUS_ACTIVE,
			'dateStart'	=> '<= '.date( 'Y-m-d' ),
			'dateEnd'	=> '>= '.date( 'Y-m-d' )
		] );
		if( [] === $messages )
			return FALSE;

		foreach( $messages as $message ){
			/** @var Entity_Notification_Recipient|NULL $recipient */
			$recipient	= $modelRecipient->getByIndices( [
				'notificationMessageId'	=> $message->notificationMessageId,
				'userId'				=> $currentUserId,
				'status'				=> Model_Notification_Recipient::STATUS_NEW,
			] );
			if( NULL !== $recipient ){
				$from	= $this->env->getRequest()->getUrl( FALSE );
				$this->env->getSession()->set( 'work_notification_id', $recipient->notificationRecipientId );
				$this->env->getSession()->set( 'work_notification_from', $from );
				self::redirect( $this->env, 'work/notification', 'view' );
				return TRUE;
			}
		}
		return FALSE;
	}
}
