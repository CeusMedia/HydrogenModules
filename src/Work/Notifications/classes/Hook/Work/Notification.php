<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Notification extends Hook
{
	public function onAppControl(): bool
	{
		/** @var HttpRequest $request */
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();

		try{
			$logicAuth		= Logic_Authentication::getInstance( $this->env );
			$roleId			= $logicAuth->getCurrentRoleId();
			if( !$logicAuth->isAuthenticated() )
				return FALSE;
			if( !$this->env->getAcl()->hasRight( $roleId, 'work/notification', 'view' ) )
				return FALSE;

			$currentUserId	= $logicAuth->getCurrentUserId();
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
			] );
			if( [] === $messages )
				return FALSE;

			foreach( $messages as $message ){
				if( NULL !== $message->dateEnd && strtotime( $message->dateEnd ) < time() )
					continue;

				/** @var Entity_Notification_Recipient|NULL $recipient */
				$recipient	= $modelRecipient->getByIndices( [
					'notificationMessageId'	=> $message->notificationMessageId,
					'userId'				=> $currentUserId,
					'status'				=> Model_Notification_Recipient::STATUS_NEW,
				] );
				if( NULL !== $recipient ){
					$session->set( 'work_notification_id', $recipient->notificationMessageId );
					$session->set( 'work_notification_from', $request->getPath() );
					self::redirect( $this->env, 'work/notification', 'view' );
					return TRUE;
				}
			}
		}
		catch( Throwable $e ){
			$this->env->getLog()->logException( $e );
		}
		return FALSE;
	}
}
