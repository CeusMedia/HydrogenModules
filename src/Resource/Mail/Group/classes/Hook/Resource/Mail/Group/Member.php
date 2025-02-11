<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_Mail_Group_Member extends Hook
{
	public function onMemberActivated(): void
	{
		$payload		= (object) $this->getPayload();
		$modelGroup		= new Model_Mail_Group( $this->env );
		$modelMember	= new Model_Mail_Group_Member( $this->env );
		$modelUser		= new Model_User( $this->env );
		$logicGroup		= Logic_Mail_Group::getInstance( $this->env );
		$logicMail		= Logic_Mail::getInstance( $this->env );

		$payload->group			= $this->checkGroupPayload( $this->env, $payload );
		$payload->member		= $this->checkMemberPayload( $this->env, $payload );
		$payload->greeting		= property_exists( $payload, 'action' ) ? $payload->action->message : '';
		$payload->informMembers	= property_exists( $payload, 'informMembers' ) ? $payload->informMembers : NULL;

		$mail		= new Mail_Info_Mail_Group_Member_Activated( $this->env, (array) $payload );
		$receiver	= (object) [
			'username'	=> $payload->member->title,
			'email'		=> $payload->member->address
		];
		$language		= $this->env->getLanguage()->getLanguage();
		$logicMail->appendRegisteredAttachments( $mail, $language );
		$logicMail->handleMail( $mail, $receiver, $language );

		$manager	= $modelUser->get( $payload->group->managerId );
		$members	= $logicGroup->getGroupMembers( $payload->group->mailGroupId, TRUE );
		foreach( $members as $item ){
			if( $item->address == $manager->email )
				continue;
			if( $item->mailGroupMemberId == $payload->member->mailGroupMemberId )
				continue;
			$data		= array_merge( (array) $payload, ['user' => $item] );
			$mail		= new Mail_Info_Mail_Group_Members_MemberActivated( $this->env, $data );
			$receiver	= (object) [
				'username'	=> $item->title,
				'email'		=> $item->address
			];
			$language	= $this->env->getLanguage()->getLanguage();
			$logicMail->appendRegisteredAttachments( $mail, $language );
			$logicMail->handleMail( $mail, $receiver, $language );
		}
	}

	// @todo call this event
	public function onMemberDeactivated(): void
	{
		$payload		= (object) $this->getPayload();
		$modelGroup		= new Model_Mail_Group( $this->env );
		$modelMember	= new Model_Mail_Group_Member( $this->env );
		$modelUser		= new Model_User( $this->env );
		$logicGroup		= Logic_Mail_Group::getInstance( $this->env );
		$logicMail		= Logic_Mail::getInstance( $this->env );

		$payload->group			= $this->checkGroupPayload( $this->env, $payload );
		$payload->member		= $this->checkMemberPayload( $this->env, $payload );
		$payload->greeting		= property_exists( $payload, 'action' ) ? $payload->action->message : '';
		$payload->informMembers	= property_exists( $payload, 'informMembers' ) ? $payload->informMembers : NULL;

		$mail		= new Mail_Info_Mail_Group_Member_Deactivated( $this->env, (array) $payload );
		$receiver	= (object) [
			'username'	=> $payload->member->title,
			'email'		=> $payload->member->address
		];
		$language		= $this->env->getLanguage()->getLanguage();
		$logicMail->appendRegisteredAttachments( $mail, $language );
		$logicMail->handleMail( $mail, $receiver, $language );

		$manager	= $modelUser->get( $payload->group->managerId );
		$members	= $logicGroup->getGroupMembers( $payload->group->mailGroupId, TRUE );
		foreach( $members as $item ){
			if( $item->address == $manager->email )
				continue;
			if( $item->mailGroupMemberId == $payload->member->mailGroupMemberId )
				continue;
			$data		= array_merge( (array) $payload, ['user' => $item] );
			$mail		= new Mail_Info_Mail_Group_Members_MemberDeactivated( $this->env, $data );
			$receiver	= (object) [
				'username'	=> $item->title,
				'email'		=> $item->address
			];
			$language	= $this->env->getLanguage()->getLanguage();
			$logicMail->appendRegisteredAttachments( $mail, $language );
			$logicMail->handleMail( $mail, $receiver, $language );
		}
	}

	public function onMemberReject(): void
	{
		$payload		= (object) $this->getPayload();
		$modelGroup		= new Model_Mail_Group( $this->env );
		$modelMember	= new Model_Mail_Group_Member( $this->env );
		$modelUser		= new Model_User( $this->env );
		$logicGroup		= Logic_Mail_Group::getInstance( $this->env );
		$logicMail		= Logic_Mail::getInstance( $this->env );

		$payload->group		= $this->checkGroupPayload( $this->env, $payload );
		$payload->member	= $this->checkMemberPayload( $this->env, $payload );

		$mail		= new Mail_Info_Mail_Group_Member_Rejected( $this->env, (array) $payload );
		$receiver	= (object) [
			'username'	=> $payload->member->title,
			'email'		=> $payload->member->address
		];
		$language		= $this->env->getLanguage()->getLanguage();
		$logicMail->appendRegisteredAttachments( $mail, $language );
		$logicMail->handleMail( $mail, $receiver, $language );
	}

	protected function checkGroupPayload( Environment $env, $payload )
	{
		if( property_exists( $payload, 'group' ) && is_object( $payload->group ) )
			return $payload->group;
		$logicGroup		= Logic_Mail_Group::getInstance( $env );
		if( property_exists( $payload, 'groupId' ) && $payload->groupId )
			return $logicGroup->getGroup( $payload->groupId );
		throw new DomainException( 'No group data set' );
	}

	protected function checkMemberPayload( Environment $env, $payload )
	{
		if( property_exists( $payload, 'member' ) && is_object( $payload->member ) )
			return $payload->member;
		$logicGroup		= Logic_Mail_Group::getInstance( $env );
		if( property_exists( $payload, 'memberId' ) && $payload->memberId )
			return $logicGroup->getGroupMember( $payload->memberId );
		throw new DomainException( 'No member data set' );
	}


	// @todo call this event
	public function onMemberJoined(): void
	{
		// @todo implement
	}

	// @todo call this event
	public function onMemberLeft(): void
	{
		// @todo implement
	}
}
