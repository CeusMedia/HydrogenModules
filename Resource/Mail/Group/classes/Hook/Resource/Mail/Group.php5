<?php
class Hook_Resource_Mail_Group{

	static public function onConfirmAfterJoin( $env, $context, $module, $data = array() ){
		$modelGroup		= new Model_Mail_Group( $env );
		$modelMember	= new Model_Mail_Group_Member( $env );
		$modelUser		= new Model_User( $env );
		$logic			= new Logic_Mail_Group( $env );
		$logicMail		= Logic_Mail::getInstance( $env );
		$action			= $data['action'];
		$group			= $modelGroup->get( $action->mailGroupId );

		if( in_array( $group->type, array( Model_Mail_Group::TYPE_INVITE, Model_Mail_Group::TYPE_PUBLIC ) ) ){
			$modelMember->edit( $action->mailGroupMemberId, array(
				'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
				'modifiedAt'	=> time(),
			) );
			$env->getMessenger()->noteSuccess( 'Ihr Beitritt wurde bestätigt und freigegeben. Sie können jetzt E-Mails an die Gruppe schicken.' );
			$manager	= $modelUser->get( $group->managerId );
			$mailData	= array(
				'group'		=> $group,
				'member'	=> $modelMember->get( $action->mailGroupMemberId ),
				'greeting'	=> $action->message,
			);

			$mail		= new Mail_Info_Mail_Group_Manager_MemberJoined( $env, $mailData );
			$language	= $env->getLanguage()->getLanguage();
			$logicMail->appendRegisteredAttachments( $mail, $language );
			$logicMail->handleMail( $mail, $manager, $language );

			$members	= $logic->getGroupMembers( $action->mailGroupId, TRUE );
			foreach( $members as $entry ){
				if( $entry->address === $manager->email )
					continue;
				if( $entry->mailGroupMemberId === $action->mailGroupMemberId )
					continue;
				$logicMail->handleMail(
					new Mail_Info_Mail_Group_Members_MemberJoined( $env, $mailData ),
					(object) array( 'email' => $entry->address ),
					$env->getLanguage()->getLanguage()
				);
			}

			$mail		= new Mail_Info_Mail_Group_Activated( $env, $mailData );
			$member		= $modelMember->get( $action->mailGroupMemberId );
			$receiver	= (object) array( 'email' => $member->address );
			$language	= $env->getLanguage()->getLanguage();
			$logicMail->appendRegisteredAttachments( $mail, $language );
			$logicMail->handleMail( $mail, $receiver, $language );
			return TRUE;
		}

		if( $group->type == Model_Mail_Group::TYPE_JOIN ){
			$modelMember->edit( $action->mailGroupMemberId, array(
				'status'		=> Model_Mail_Group_Member::STATUS_CONFIRMED,
				'modifiedAt'	=> time(),
			) );
			$env->getMessenger()->noteSuccess( 'Ihr Beitritt wurde bestätigt. Die Freigabe durch den Verwalter steht noch aus.' );

			$manager	= $modelUser->get( $group->managerId );
			$mailData	= array(
				'group'		=> $group,
				'member'	=> $modelMember->get( $action->mailGroupMemberId ),
				'greeting'	=> $action->message,
			);

			$mail		= new Mail_Info_Mail_Group_Manager_MemberJoined( $env, $mailData );
			$language	= $env->getLanguage()->getLanguage();
			$logicMail->appendRegisteredAttachments( $mail, $language );
			$logicMail->handleMail( $mail, $manager, $language );

			$logic->registerMemberAction(
				'activateAfterConfirm',
				$action->mailGroupId,
				$action->mailGroupMemberId,
				$action->message
			);
			return TRUE;
		}
		else if( $group->type == Model_Mail_Group::TYPE_REGISTER ){
			$modelMember->edit( $action->mailGroupMemberId, array(
				'status'		=> Model_Mail_Group_Member::STATUS_CONFIRMED,
				'modifiedAt'	=> time(),
			) );
			$env->getMessenger()->noteSuccess( 'Ihr Beitritt wurde bestätigt. Die Freigabe durch den Verwalter steht noch aus.' );

			$manager	= $modelUser->get( $group->managerId );
			$mailData	= array(
				'group'		=> $group,
				'member'	=> $modelMember->get( $action->mailGroupMemberId ),
				'greeting'	=> $action->message,
			);

			$mail		= new Mail_Info_Mail_Group_Manager_MemberRegistered( $env, $mailData );
			$language	= $env->getLanguage()->getLanguage();
			$logicMail->appendRegisteredAttachments( $mail, $language );
			$logicMail->handleMail( $mail, $manager, $language );
			return TRUE;
		}

		return FALSE;
	}

	static public function onDeactivateAfterLeaving(  $env, $context, $module, $data = array() ){
		$modelGroup		= new Model_Mail_Group( $env );
		$modelMember	= new Model_Mail_Group_Member( $env );
		$modelUser		= new Model_User( $env );
		$logic			= new Logic_Mail_Group( $env );
		$logicMail		= Logic_Mail::getInstance( $env );
		$action			= $data['action'];
		$group			= $modelGroup->get( $action->mailGroupId );

		$modelMember->edit( $action->mailGroupMemberId, array(
			'status'		=> Model_Mail_Group_Member::STATUS_UNREGISTERED,
			'modifiedAt'	=> time(),
		) );
		$env->getMessenger()->noteSuccess( 'Ihr Austritt ist nun vollständig. Sie erhalten ab jetzt keine weiteren E-Mails von der Gruppe.' );

		$manager	= $modelUser->get( $group->managerId );
		$mailData	= array(
			'group'		=> $group,
			'member'	=> $modelMember->get( $action->mailGroupMemberId ),
			'greeting'	=> $action->message,
		);

		$mail		= new Mail_Info_Mail_Group_Manager_MemberLeft( $env, $mailData );
		$language	= $env->getLanguage()->getLanguage();
		$logicMail->appendRegisteredAttachments( $mail, $language );
		$logicMail->handleMail( $mail, $manager, $language );

		$members	= $logic->getGroupMembers( $action->mailGroupId, TRUE );
		foreach( $members as $entry ){
			if( $entry->address === $manager->email )
				continue;
			if( $entry->mailGroupMemberId === $action->mailGroupMemberId )
				continue;
			$logicMail->handleMail(
				new Mail_Info_Mail_Group_Members_MemberLeft( $env, $mailData ),
				(object) array( 'email' => $entry->address ),
				$env->getLanguage()->getLanguage()
			);
		}

		$member		= $modelMember->get( $action->mailGroupMemberId );
		$mail		= new Mail_Info_Mail_Group_Left( $env, $mailData );
		$receiver	= (object) array( 'email' => $member->address );
		$language	= $env->getLanguage()->getLanguage();
		$logicMail->appendRegisteredAttachments( $mail, $language );
		$logicMail->handleMail( $mail, $receiver, $language );
		return TRUE;
	}
}
