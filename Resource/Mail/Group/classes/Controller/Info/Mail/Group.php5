<?php
class Controller_Info_Mail_Group extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= new Logic_Mail_Group( $this->env );

		$this->modelGroup	= new Model_Mail_Group( $this->env );
		$this->modelMember	= new Model_Mail_Group_Member( $this->env );

	}

	public function index(){
		$this->restart( 'register', TRUE );
	}

	public function register(){
/*		if( $this->request->has( 'save' ) ){
		}*/

		$groups	= $this->logic->getActiveGroups();
/*		foreach( $groups as $nr => $groups )
			if( $group->... == ... )
				unset( $groups[$nr] );*/
		$this->addData( 'groups', $groups );
	}

	public function unregister(){
		$addressGroup	= $this->request->get( 'address_group' );
		$addressMember	= $this->request->get( 'address_member' );
		if( $this->request->has( 'save' ) ){
			if( !( $group = $this->modelGroup->getByIndex( 'address', $addressGroup ) ) ){
				$this->messenger->noteError( 'Keine Gruppe mit dieser E-Mail-Adresse gefunden.' );
				$this->restart( 'unregister?address_member='.$addressMember, TRUE );
			}
			$indices	= array(
				'mailGroupId'	=> $group->mailGroupId,
				'address'		=> $addressMember
			);
			if( !( $member = $this->modelMember->getByIndices( $indices ) ) ){
				$this->messenger->noteError( 'Kein Mitglied mit dieser E-Mail-Adresse gefunden.' );
				$this->restart( 'unregister?address_group='.$addressGroup, TRUE );
			}
			$this->modelMember->remove( $member->mailGroupMemberId );
			$this->messenger->noteSuccess( 'Das Mitglied wurde aus der Gruppe entfernt.' );
			$this->restart( 'unregister', TRUE );
		}
		$groups	= $this->logic->getActiveGroups();
		$this->addData( 'groups', $groups );
		$this->addData( 'address_group', $addressGroup );
		$this->addData( 'address_member', $addressMember );

	}
}
