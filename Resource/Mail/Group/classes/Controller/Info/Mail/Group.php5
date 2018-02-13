<?php
class Controller_Info_Mail_Group extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= new Logic_Mail_Group( $this->env );

		$this->modelGroup	= new Model_Mail_Group( $this->env );
		$this->modelMember	= new Model_Mail_Group_Member( $this->env );

	}

	protected function checkId( $groupId ){
		try{
			return $this->logic->getGroup( $groupId );
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Ungültige Gruppe. Weiterleitung zur Übersicht.' );
			$this->restart( NULL, TRUE );
		}
	}

	public function index( $page = 0, $limit = 10 ){
		$groups	= $this->logic->getGroups( !TRUE );
		foreach( $groups as $group )
			$group->members	= $this->logic->getGroupMembers( $group->mailGroupId );
		$this->addData( 'groups', $groups );
	}

	public function join( $groupId ){
		$group	= $this->checkId( $groupId );
		$group->members	= $this->logic->getGroupMembers( $groupId );
		if( $this->request->has( 'save' ) ){
		}
		$this->addData( 'group', $group );
	}

	public function register( $groupId ){
		$group	= $this->checkId( $groupId );
		if( $this->request->has( 'save' ) ){
		}
		$this->addData( 'group', $group );
	}

	public function unregister( $groupId ){
		$this->checkId( $groupId );
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
