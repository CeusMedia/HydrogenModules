<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_Mail extends Hook
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException|ReflectionException
	 */
	public function onListUserRelations(): void
	{
		if( empty( $this->payload['userId'] ) ){
			$message	= 'Hook "Hook_Info_Mail::onListUserRelations" is missing user ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}
		$modelUser		= new Model_User( $this->env );
		if( !$modelUser->has( $this->payload['userId'] ) )
			return;

		$linkable		= $this->payload['linkable'] ?? FALSE;
		$activeOnly		= $this->payload['activeOnly'] ?? FALSE;
		/** @var Logic_Authentication $auth */
		$auth			= Logic_Authentication::getInstance( $this->env );
		$linkController	= NULL;
		$linkAction		= NULL;
		if( $linkable && $auth->isAuthenticated() ){
			$acl			= $this->env->getAcl();
			$viewerRoleId	= $auth->getCurrentRoleId();
			if( $acl->hasRight( $viewerRoleId, 'admin/mail/queue', 'view' ) ){
				$linkController	= 'Admin_Mail_Queue';
				$linkAction		= 'view';
			}
			else if( $acl->hasRight( $viewerRoleId, 'manage/my/mail', 'view' ) ){
				$linkController	= 'Manage_My_Mail';
				$linkAction		= 'view';
			}
			else
				$linkable		= FALSE;
		}

		$modelMail		= new Model_Mail( $this->env );
		$orders			= ['mailId' => 'DESC'];
		$fields			= ['mailId', 'status', 'subject', 'enqueuedAt'];
		$icon			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-envelope-o', 'title' => 'E-Mail'] );
		$statusesActive	= [
			Model_Mail::STATUS_SENT,
			Model_Mail::STATUS_RECEIVED,
			Model_Mail::STATUS_OPENED,
			Model_Mail::STATUS_REPLIED,
			Model_Mail::STATUS_ARCHIVED,
		];
		$words			= $this->env->getLanguage()->getWords( 'mail' );

		//  RECEIVED MAILS
		$indices	= ['receiverId' => $this->payload['userId']];
		if( $activeOnly )
			$indices['status']	= $statusesActive;
		$list		= [];
		$mails	 	= $modelMail->getAll( $indices, $orders, [], $fields );
		/** @var object{mailId: int, status: int, 'subject: string, enqueuedAt: int} $mail */
		foreach( $mails as $mail )
			$list[]		= (object) [
				'id'		=> $linkable ? $mail->mailId : NULL,
				'label'		=> $icon.'&nbsp;'.$mail->subject,
			];
		View_Helper_ItemRelationLister::enqueueRelations(
			$this->payload,																			//  hook content data
			$this->module,																			//  module called by hook
			'entity',																			//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label-received'],												//  label of type of related items
			$linkController,																		//  controller of entity
			$linkAction																				//  action to view or edit entity
		);

		//  SENT MAILS
		$indices	= ['senderId' => $this->payload['userId']];
		if( $activeOnly )
			$indices['status']	= $statusesActive;
		$list		= [];

		$mails	 	= $modelMail->getAll( $indices, $orders, [], $fields );
		/** @var object{mailId: int, status: int, 'subject: string, enqueuedAt: int} $mail */
		foreach( $mails as $mail )
			$list[]		= (object) [
				'id'		=> $linkable ? $mail->mailId : NULL,
				'label'		=> $icon.'&nbsp;'.$mail->subject,
			];
		View_Helper_ItemRelationLister::enqueueRelations(
			$this->payload,																			//  hook content data
			$this->module,																			//  module called by hook
			'entity',																			//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label-sent'],													//  label of type of related items
			$linkController,																		//  controller of entity
			$linkAction																				//  action to view or edit entity
		);
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onUserRemove(): void
	{
		if( empty( $data->userId ) ){
			$message	= 'Hook "Hook_Info_Mail::onUserRemove" is missing user ID in data.';
			$this->env->getMessenger()->noteFailure( $message );
			return;
		}

		$modelMail	= new Model_Mail( $this->env );
		$orders		= ['mailId' => 'ASC'];
		$fields		= ['mailId'];

		$indices	= ['senderId' => $data->userId];
		/** @var array<object{mailId: int}> $mailsSent */
		$mailsSent	= $modelMail->getAll( $indices, $orders, [], $fields );
		foreach( $mailsSent as $mailId )
			$modelMail->remove( $mailId );

		$indices	= ['receiverId' => $data->userId];
		/** @var array<object{mailId: int}> $mailsReceived */
		$mailsReceived	= $modelMail->getAll( $indices, $orders, [], $fields );
		foreach( $mailsReceived as $mailId )
			$modelMail->remove( $mailId );
		if( isset( $this->payload['counts'] ) )
			$this->payload['counts']['Resource_Mail']	= (object) [
				'entities' => count( $mailsSent ) + count( $mailsReceived )
			];
	}
}
