<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_Mail_Group extends Hook
{
	public function onGroupActivated(): void
	{
		$logicGroup		= new Logic_Mail_Group( $this->env );
		$logicMail		= Logic_Mail::getInstance( $this->env );

		$payload	= (object) $this->getPayload();
		if( property_exists( $payload, 'group' ) && is_object( $payload->group ) )
			$group	= $payload->group;
		else if( property_exists( $payload, 'groupId' ) && $payload->groupId )
			$group	= $logicGroup->getGroup( $payload->groupId );
		else
			throw new DomainException( 'No group data set' );

	//	@todo implement
	}

	public function onGroupDeactivated(): void
	{
		$logicGroup		= new Logic_Mail_Group( $this->env );
		$logicMail		= Logic_Mail::getInstance( $this->env );

		$payload	= (object) $this->getPayload();
		if( property_exists( $payload, 'group' ) && is_object( $payload->group ) )
			$group	= $payload->group;
		else if( property_exists( $payload, 'groupId' ) && $payload->groupId )
			$group	= $logicGroup->getGroup( $payload->groupId );
		else
			throw new DomainException( 'No group data set' );

	//	@todo implement
	}


	public function onConfirmAfterJoin(): bool
	{
		$modelGroup		= new Model_Mail_Group( $this->env );
		$modelMember	= new Model_Mail_Group_Member( $this->env );
		$modelUser		= new Model_User( $this->env );
		$logicGroup		= Logic_Mail_Group::getInstance( $this->env );
		$logicMail		= Logic_Mail::getInstance( $this->env );

		$data			= (array) $this->getPayload();
		$action			= $data['action'];
		$group			= $modelGroup->get( $action->mailGroupId );

		$member		= $logicGroup->checkMemberId( $action->mailGroupMemberId );
		if( $member->status == Model_Mail_Group_Member::STATUS_REJECTED ){
			$this->env->getMessenger()->noteError( 'Ihr Beitritt wurde bereits vom Verwalter der Gruppe abgelehnt.' );
			return TRUE;
		}

		if( in_array( $group->type, [Model_Mail_Group::TYPE_INVITE, Model_Mail_Group::TYPE_AUTOJOIN] ) ){
			$modelMember->edit( $action->mailGroupMemberId, array(
				'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
				'modifiedAt'	=> time(),
			) );
			$this->env->getMessenger()->noteSuccess( 'Ihr Beitritt wurde bestätigt und freigegeben. Sie können jetzt E-Mails an die Gruppe schicken.' );
			$manager	= $modelUser->get( $group->managerId );
			$mailData	= array(
				'group'		=> $group,
				'member'	=> $modelMember->get( $action->mailGroupMemberId ),
				'greeting'	=> $action->message,
			);

			$mail		= new Mail_Info_Mail_Group_Manager_MemberJoined( $this->env, $mailData );
			$language	= $this->env->getLanguage()->getLanguage();
			$logicMail->appendRegisteredAttachments( $mail, $language );
			$logicMail->handleMail( $mail, $manager, $language );

			$members	= $logicGroup->getGroupMembers( $action->mailGroupId, TRUE );
			foreach( $members as $entry ){
				if( $entry->address === $manager->email )
					continue;
				if( $entry->mailGroupMemberId === $action->mailGroupMemberId )
					continue;
				$logicMail->handleMail(
					new Mail_Info_Mail_Group_Members_MemberJoined( $this->env, $mailData ),
					(object) ['email' => $entry->address],
					$this->env->getLanguage()->getLanguage()
				);
			}
/*
			$mail		= new Mail_Info_Mail_Group_Activated( $env, $mailData );
			$member		= $modelMember->get( $action->mailGroupMemberId );
			$receiver	= (object) ['email' => $member->address];
			$language	= $env->getLanguage()->getLanguage();
			$logicMail->appendRegisteredAttachments( $mail, $language );
			$logicMail->handleMail( $mail, $receiver, $language );*/
			return TRUE;
		}

		if( $group->type == Model_Mail_Group::TYPE_JOIN ){
			$modelMember->edit( $action->mailGroupMemberId, array(
				'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
				'modifiedAt'	=> time(),
			) );
			$this->env->getMessenger()->noteSuccess( 'Ihr Beitritt wurde bestätigt. Sie können jetzt mit der Gruppe kommunizieren.' );

			$manager	= $modelUser->get( $group->managerId );
			$mailData	= array(
				'group'		=> $group,
				'member'	=> $modelMember->get( $action->mailGroupMemberId ),
				'greeting'	=> $action->message,
			);

			$mail		= new Mail_Info_Mail_Group_Manager_MemberJoined( $this->env, $mailData );
			$language	= $this->env->getLanguage()->getLanguage();
			$logicMail->appendRegisteredAttachments( $mail, $language );
			$logicMail->handleMail( $mail, $manager, $language );

			$members	= $logicGroup->getGroupMembers( $action->mailGroupId, TRUE );
			foreach( $members as $entry ){
				if( $entry->address === $manager->email )
					continue;
				if( $entry->mailGroupMemberId === $action->mailGroupMemberId )
					continue;
				$logicMail->handleMail(
					new Mail_Info_Mail_Group_Members_MemberJoined( $this->env, $mailData ),
					(object) ['email' => $entry->address],
					$this->env->getLanguage()->getLanguage()
				);
			}
/*
			$logicGroup->registerMemberAction(
				'activateAfterConfirm',
				$action->mailGroupId,
				$action->mailGroupMemberId,
				$action->message
			);*/
			return TRUE;
		}
		else if( $group->type == Model_Mail_Group::TYPE_REGISTER ){
			$modelMember->edit( $action->mailGroupMemberId, array(
				'status'		=> Model_Mail_Group_Member::STATUS_CONFIRMED,
				'modifiedAt'	=> time(),
			) );
			$this->env->getMessenger()->noteSuccess( 'Ihr Beitritt wurde bestätigt. Die Freigabe durch den Verwalter steht noch aus.' );

			$manager	= $modelUser->get( $group->managerId );
			$mailData	= array(
				'group'		=> $group,
				'member'	=> $modelMember->get( $action->mailGroupMemberId ),
				'greeting'	=> $action->message,
			);

			$mail		= new Mail_Info_Mail_Group_Manager_MemberRegistered( $this->env, $mailData );
			$language	= $this->env->getLanguage()->getLanguage();
			$logicMail->appendRegisteredAttachments( $mail, $language );
			$logicMail->handleMail( $mail, $manager, $language );
			return TRUE;
		}

		return FALSE;
	}

	public function onDeactivateAfterLeaving(): bool
	{
		$modelGroup		= new Model_Mail_Group( $this->env );
		$modelMember	= new Model_Mail_Group_Member( $this->env );
		$modelUser		= new Model_User( $this->env );
		$logicGroup		= new Logic_Mail_Group( $this->env );
		$logicMail		= Logic_Mail::getInstance( $this->env );

		$data			= (array) $this->getPayload();
		$action			= $data['action'];
		$group			= $modelGroup->get( $action->mailGroupId );

		$modelMember->edit( $action->mailGroupMemberId, array(
			'status'		=> Model_Mail_Group_Member::STATUS_UNREGISTERED,
			'modifiedAt'	=> time(),
		) );
		$this->env->getMessenger()->noteSuccess( 'Ihr Austritt ist nun vollständig. Sie erhalten ab jetzt keine weiteren E-Mails von der Gruppe.' );

		$manager	= $modelUser->get( $group->managerId );
		$mailData	= array(
			'group'		=> $group,
			'member'	=> $modelMember->get( $action->mailGroupMemberId ),
			'greeting'	=> $action->message,
		);

		$mail		= new Mail_Info_Mail_Group_Manager_MemberLeft( $this->env, $mailData );
		$language	= $this->env->getLanguage()->getLanguage();
		$logicMail->appendRegisteredAttachments( $mail, $language );
		$logicMail->handleMail( $mail, $manager, $language );

		$members	= $logicGroup->getGroupMembers( $action->mailGroupId, TRUE );
		foreach( $members as $entry ){
			if( $entry->address === $manager->email )
				continue;
			if( $entry->mailGroupMemberId === $action->mailGroupMemberId )
				continue;
			$logicMail->handleMail(
				new Mail_Info_Mail_Group_Members_MemberLeft( $this->env, $mailData ),
				(object) ['email' => $entry->address],
				$this->env->getLanguage()->getLanguage()
			);
		}

		$member		= $modelMember->get( $action->mailGroupMemberId );
		$mail		= new Mail_Info_Mail_Group_Member_Left( $this->env, $mailData );
		$receiver	= (object) ['email' => $member->address];
		$language	= $this->env->getLanguage()->getLanguage();
		$logicMail->appendRegisteredAttachments( $mail, $language );
		$logicMail->handleMail( $mail, $receiver, $language );
		return TRUE;
	}
}
