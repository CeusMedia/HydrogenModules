<?php
class Hook_Resource_Mail_Group{

	static public function onConfirmAfterJoin( $env, $context, $module, $data = array() ){
		$modelGroup		= new Model_Mail_Group( $env );
		$modelMember	= new Model_Mail_Group_Member( $env );
		$modelUser		= new Model_User( $env );
		$logic			= new Logic_Mail_Group( $env );
		$logicMail		= new Logic_Mail( $env );
		$action			= $data['action'];
		$group			= $modelGroup->get( $action->mailGroupId );

		if( $group->type == Model_Mail_Group::TYPE_JOIN ){
			$modelMember->edit( $action->mailGroupMemberId, array(
				'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
				'modifiedAt'	=> time(),
			) );
			$env->getMessenger()->noteSuccess( 'Ihr Beitritt ist nun vollständig. Sie können jetzt E-Mails an die Gruppe schreiben.' );

			$manager	= $modelUser->get( $group->managerId );
			$mailData	= array(
				'group'		=> $group,
				'member'	=> $modelMember->get( $action->mailGroupMemberId ),
				'greeting'	=> $action->message,
			);

			$logicMail->handleMail(
				new Mail_Info_Mail_Group_Manager_MemberJoined( $env, $mailData ),
				$manager,
				$env->getLanguage()->getLanguage()
			);

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

			$member	= $modelMember->get( $action->mailGroupMemberId );
			$logicMail->handleMail(
				new Mail_Info_Mail_Group_Joined( $env, $mailData ),
				(object) array( 'email' => $member->address ),
				$env->getLanguage()->getLanguage()
			);
			return TRUE;
		}
		return FALSE;
	}

	static public function onDeactivateAfterLeaving(  $env, $context, $module, $data = array() ){
		$modelGroup		= new Model_Mail_Group( $env );
		$modelMember	= new Model_Mail_Group_Member( $env );
		$modelUser		= new Model_User( $env );
		$logic			= new Logic_Mail_Group( $env );
		$logicMail		= new Logic_Mail( $env );
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

		$logicMail->handleMail(
			new Mail_Info_Mail_Group_Manager_MemberLeft( $env, $mailData ),
			$manager,
			$env->getLanguage()->getLanguage()
		);

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

		$member	= $modelMember->get( $action->mailGroupMemberId );
		$logicMail->handleMail(
			new Mail_Info_Mail_Group_Left( $env, $mailData ),
			(object) array( 'email' => $member->address ),
			$env->getLanguage()->getLanguage()
		);
		return TRUE;
	}
}
