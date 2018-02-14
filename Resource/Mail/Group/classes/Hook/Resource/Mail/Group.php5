<?php
class Hook_Resource_Mail_Group{

	static public function onActivateAfterJoin( $env, $context, $module, $data = array()){
		$modelGroup		= new Model_Mail_Group( $env );
		$modelMember	= new Model_Mail_Group_Member( $env );
		$logic			= new Logic_Mail_Group( $env );
		$action			= $data['action'];
		$group			= $modelGroup->get( $action->mailGroupId );
		if( $group->type == Model_Mail_Group::TYPE_JOIN ){
			$modelMember->edit( $action->mailGroupMemberId, array(
				'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
				'modifiedAt'	=> time(),
			) );
			$env->getMessenger()->noteSuccess( 'Ihr Beitritt ist nun vollständig. Sie können jetzt E-Mails an die Gruppe schreiben.' );
			$logic->informGroupManagerAboutJoinedMember( $action->mailGroupId, $action->mailGroupMemberId, $action->message );
			$logic->informGroupMembersAboutNewMember( $group->mailGroupId, $action->mailGroupMemberId, $action->message );
		}
		return TRUE;
	}
}
