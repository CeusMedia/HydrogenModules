<?php
class Hook_Resource_Mail_Group_Member /*extends CMF_Hydrogen_Hook*/{

	static protected function checkGroupPayload( $env, $payload ){
		if( property_exists( $payload, 'group' ) && is_object( $payload->group ) )
			return $payload->group;
		$logicGroup		= Logic_Mail_Group::getInstance( $env );
		if( property_exists( $payload, 'groupId' ) && $payload->groupId )
			return $logicGroup->getGroup( $payload->groupId );
		throw new DomainException( 'No group data set' );
	}

	static protected function checkMemberPayload( $env, $payload ){
		if( property_exists( $payload, 'member' ) && is_object( $payload->member ) )
			return $payload->member;
		$logicGroup		= Logic_Mail_Group::getInstance( $env );
		if( property_exists( $payload, 'memberId' ) && $payload->memberId )
			return $logicGroup->getGroupMember( $group->mailGroupId, $payload->memberId );
		throw new DomainException( 'No member data set' );
	}

	static public function onMemberActivated( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$payload		= (object) $data;
		$modelGroup		= new Model_Mail_Group( $env );
		$modelMember	= new Model_Mail_Group_Member( $env );
		$modelUser		= new Model_User( $env );
		$logicGroup		= Logic_Mail_Group::getInstance( $env );
		$logicMail		= Logic_Mail::getInstance( $env );

		$payload->group			= self::checkGroupPayload( $env, $payload );
		$payload->member		= self::checkMemberPayload( $env, $payload );
		$payload->greeting		= property_exists( $payload, 'action' ) ? $payload->action->message : '';
		$payload->informMembers	= property_exists( $payload, 'informMembers' ) ? $payload->informMembers : NULL;

		$mail		= new Mail_Info_Mail_Group_Member_Activated( $env, (array) $payload );
		$receiver	= (object) array(
			'username'	=> $payload->member->title,
			'email'		=> $payload->member->address
		);
		$language		= $env->getLanguage()->getLanguage();
		$logicMail->appendRegisteredAttachments( $mail, $language );
		$logicMail->handleMail( $mail, $receiver, $language );

		$manager	= $modelUser->get( $payload->group->managerId );
		$members	= $logicGroup->getGroupMembers( $payload->group->mailGroupId, TRUE );
		foreach( $members as $item ){
			if( $item->address == $manager->email )
				continue;
			if( $item->mailGroupMemberId == $payload->member->mailGroupMemberId )
				continue;
			$data		= array_merge( (array) $payload, array( 'user' => $item ) );
			$mail		= new Mail_Info_Mail_Group_Members_MemberActivated( $env, $data );
			$receiver	= (object) array(
				'username'	=> $item->title,
				'email'		=> $item->address
			);
			$language	= $env->getLanguage()->getLanguage();
			$logicMail->appendRegisteredAttachments( $mail, $language );
			$logicMail->handleMail( $mail, $receiver, $language );
		}
	}

	// @todo call this event
	static public function onMemberDeactivated( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$payload		= (object) $data;
		$modelGroup		= new Model_Mail_Group( $env );
		$modelMember	= new Model_Mail_Group_Member( $env );
		$modelUser		= new Model_User( $env );
		$logicGroup		= Logic_Mail_Group::getInstance( $env );
		$logicMail		= Logic_Mail::getInstance( $env );

		$payload->group			= self::checkGroupPayload( $env, $payload );
		$payload->member		= self::checkMemberPayload( $env, $payload );
		$payload->greeting		= property_exists( $payload, 'action' ) ? $payload->action->message : '';
		$payload->informMembers	= property_exists( $payload, 'informMembers' ) ? $payload->informMembers : NULL;

		$mail		= new Mail_Info_Mail_Group_Member_Deactivated( $env, (array) $payload );
		$receiver	= (object) array(
			'username'	=> $payload->member->title,
			'email'		=> $payload->member->address
		);
		$language		= $env->getLanguage()->getLanguage();
		$logicMail->appendRegisteredAttachments( $mail, $language );
		$logicMail->handleMail( $mail, $receiver, $language );

		$manager	= $modelUser->get( $payload->group->managerId );
		$members	= $logicGroup->getGroupMembers( $payload->group->mailGroupId, TRUE );
		foreach( $members as $item ){
			if( $item->address == $manager->email )
				continue;
			if( $item->mailGroupMemberId == $payload->member->mailGroupMemberId )
				continue;
			$data		= array_merge( (array) $payload, array( 'user' => $item ) );
			$mail		= new Mail_Info_Mail_Group_Members_MemberDeactivated( $env, $data );
			$receiver	= (object) array(
				'username'	=> $item->title,
				'email'		=> $item->address
			);
			$language	= $env->getLanguage()->getLanguage();
			$logicMail->appendRegisteredAttachments( $mail, $language );
			$logicMail->handleMail( $mail, $receiver, $language );
		}
	}

	static public function onMemberReject( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$payload		= (object) $data;
		$modelGroup		= new Model_Mail_Group( $env );
		$modelMember	= new Model_Mail_Group_Member( $env );
		$modelUser		= new Model_User( $env );
		$logicGroup		= Logic_Mail_Group::getInstance( $env );
		$logicMail		= Logic_Mail::getInstance( $env );

		$payload->group		= self::checkGroupPayload( $env, $payload );
		$payload->member	= self::checkMemberPayload( $env, $payload );

		$mail		= new Mail_Info_Mail_Group_Member_Rejected( $env, (array) $payload );
		$receiver	= (object) array(
			'username'	=> $payload->member->title,
			'email'		=> $payload->member->address
		);
		$language		= $env->getLanguage()->getLanguage();
		$logicMail->appendRegisteredAttachments( $mail, $language );
		$logicMail->handleMail( $mail, $receiver, $language );
	}



	// @todo call this event
	static public function onMemberJoined( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		// @todo implement
	}

	// @todo call this event
	static public function onMemberLeft( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		// @todo implement
	}
}