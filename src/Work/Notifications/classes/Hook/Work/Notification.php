<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Notification extends Hook
{
	public function onAppControl(): bool
	{
		/** @var \CeusMedia\Common\Net\HTTP\Request $request */
		$request		= $this->env->getRequest();
		$auth			= Logic_Authentication::getInstance( $this->env );
		if( !$auth->isAuthenticated() )
			return FALSE;

		$currentUserId	= $auth->getCurrentUserId();
		$skipPaths		= [
			'work/notification/view',
			'auth/logout',
			'auth/local/logout',
		];
		if( in_array( $request->getPath(), $skipPaths ) )
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
				$this->env->getSession()->set( 'work_notification_id', $recipient->notificationRecipientId );
				$this->env->getSession()->set( 'work_notification_from', $request->getPath() );
				self::redirect( $this->env, 'work/notification', 'view' );
				return TRUE;
			}
		}
		return FALSE;
	}
}
