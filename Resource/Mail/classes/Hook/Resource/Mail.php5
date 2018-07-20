<?php
class Hook_Resource_Mail /*extends CMF_Hydrogen_Hook*/{

	static public function onListUserRelations( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
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
		$orders			= array( 'mailId' => 'DESC' );
		$fields			= array( 'mailId', 'status', 'subject', 'enqueuedAt' );
		$icon			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-envelope-o', 'title' => 'E-Mail' ) );
		$statusesActive	= array(
			Model_Mail::STATUS_SENT,
			Model_Mail::STATUS_RECEIVED,
			Model_Mail::STATUS_OPENED,
			Model_Mail::STATUS_REPLIED,
			Model_Mail::STATUS_ARCHIVED,
		);
		$words			= $env->getLanguage()->getWords( 'mail' );

		//  RECEIVED MAILS
		$indices	= array( 'receiverId' => $data->userId );
		if( $activeOnly )
			$indices['status']	= $statusesActive;
		$list		= array();
		$mails	 	= $modelMail->getAll( $indics, $orders, array(), $fields );
		foreach( $mails as $mail )
			$list[]		= (object) array(
				'id'		=> $linkable ? $mail->mailId : NULL,
				'label'		=> $icon.'&nbsp;'.$mail->subject,
			);
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
		$indices	= array( 'senderId' => $data->userId );
		if( $activeOnly )
			$indices['status']	= $statusesActive;
		$list		= array();

		$mails	 	= $modelMail->getAll( $indices, $orders, array(), $fields );
		foreach( $mails as $mail )
			$list[]		= (object) array(
				'id'		=> $linkable ? $mail->mailId : NULL,
				'label'		=> $icon.'&nbsp;'.$mail->subject,
			);
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

	static public function onRemoveUser( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$data	= (object) $data;
		if( empty( $data->userId ) ){
			$message	= 'Hook "Hook_Info_Mail::onRemoveUser" is missing user ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}

		$modelMail	= new Model_Mail( $env );
		if( !( $user = $modelUser->get( $data->userId ) ) )
			return;

		$orders		= array( 'mailId' => 'ASC' );
		$limits		= array();
		$fields		= array( 'mailId' );

		$indices	= array( 'senderId' => $data->userId );
		foreach( $modelMail->getAll( $indices, $orders, $limits, $fields ) as $mail )
			$modelMail->remove( $mail->mailId );

		$indices	= array( 'receiverId' => $data->userId );
		foreach( $modelMail->getAllByIndices( $indics, $orders, array(), $fields ) as $mail )
			$modelMail->remove( $mail->mailId );
	}
}
