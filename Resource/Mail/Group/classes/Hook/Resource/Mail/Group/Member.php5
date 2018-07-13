<?php
class Hook_Resource_Mail_Group_Member /*extends CMF_Hydrogen_Hook*/{

	// @todo call this event
	static public function onMemberActivated( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$data			= (object) $data;
		$modelGroup		= new Model_Mail_Group( $env );
		$modelMember	= new Model_Mail_Group_Member( $env );
		$logicGroup		= new Logic_Mail_Group( $env );
		$logicMail		= Logic_Mail::getInstance( $env );
		$greeting		= property_exists( $data->action ) ? $data->action->message : '';
		if( property_exists( $data->group ) && is_object( $data->group ) )
			$group	= $data->group;
		else if( property_exists( $data->groupId ) && $data->groupId )
			$group	= $logicGroup->getGroup( $data->groupId );
		else
			throw new DomainException( 'No group data set' );

		if( property_exists( $data->member ) && is_object( $data->member ) )
			$member	= $data->member;
		else if( property_exists( $data->memberId ) && $data->memberId )
			$member	= $logicGroup->getGroupMember( $group->mailGroupId, $data->memberId );
		else
			throw new DomainException( 'No member data set' );

		// @todo send mails to members (Mail_Info_Mail_Group_Member_Joined?) and manager

	}


	// @todo call this event
	static public function onMemberActivated( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		// @todo implement
	}

	// @todo call this event
	static public function onMemberDeactivated( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		// @todo implement
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
