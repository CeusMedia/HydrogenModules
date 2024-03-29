<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_Mail extends Hook
{
	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	static public function onListUserRelations( Environment $env, $context, $module, $data = [] )
	{
		$data	= (object) $data;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Hook_Info_Mail::onListUserRelations" is missing user ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$modelUser		= new Model_User( $env );
		if( !( $user = $modelUser->get( $data->userId ) ) )
			return;

		$linkable		= isset( $data->linkable ) ? $data->linkable : FALSE;
		$activeOnly		= isset( $data->activeOnly ) ? $data->activeOnly : FALSE;
		$auth			= Logic_Authentication::getInstance( $env );
		$linkController	= NULL;
		$linkAction		= NULL;
		if( $linkable && $auth->isAuthenticated() ){
			$acl			= $env->getAcl();
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

		$modelMail		= new Model_Mail( $env );
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
		$words			= $env->getLanguage()->getWords( 'mail' );

		//  RECEIVED MAILS
		$indices	= ['receiverId' => $data->userId];
		if( $activeOnly )
			$indices['status']	= $statusesActive;
		$list		= [];
		$mails	 	= $modelMail->getAll( $indices, $orders, [], $fields );
		foreach( $mails as $mail )
			$list[]		= (object) [
				'id'		=> $linkable ? $mail->mailId : NULL,
				'label'		=> $icon.'&nbsp;'.$mail->subject,
			];
		View_Helper_ItemRelationLister::enqueueRelations(
			$data,																					//  hook content data
			$module,																				//  module called by hook
			'entity',																				//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label-received'],												//  label of type of related items
			$linkController,																		//  controller of entity
			$linkAction																				//  action to view or edit entity
		);

		//  SENT MAILS
		$indices	= ['senderId' => $data->userId];
		if( $activeOnly )
			$indices['status']	= $statusesActive;
		$list		= [];

		$mails	 	= $modelMail->getAll( $indices, $orders, [], $fields );
		foreach( $mails as $mail )
			$list[]		= (object) [
				'id'		=> $linkable ? $mail->mailId : NULL,
				'label'		=> $icon.'&nbsp;'.$mail->subject,
			];
		View_Helper_ItemRelationLister::enqueueRelations(
			$data,																					//  hook content data
			$module,																				//  module called by hook
			'entity',																				//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label-sent'],													//  label of type of related items
			$linkController,																		//  controller of entity
			$linkAction																				//  action to view or edit entity
		);
	}

	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	static public function onUserRemove( Environment $env, $context, $module, $data = [] )
	{
		$data	= (object) $data;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Hook_Info_Mail::onUserRemove" is missing user ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}

		$modelMail	= new Model_Mail( $env );
		$orders		= ['mailId' => 'ASC'];
		$fields		= ['mailId'];

		$indices	= ['senderId' => $data->userId];
		$mailsSent	= $modelMail->getAll( $indices, $orders, [], $fields );
		foreach( $mailsSent as $mailId )
			$modelMail->remove( $mailId );

		$indices	= ['receiverId' => $data->userId];
		$mailsReceived	= $modelMail->getAll( $indices, $orders, [], $fields );
		foreach( $mailsReceived as $mailId )
			$modelMail->remove( $mailId );
		if( isset( $data->counts ) )
			$data->counts['Resource_Mail']	= (object) array(
				'entities' => count( $mailsSent ) + count( $mailsReceived )
			);
	}
}
