<?php
class Controller_Info_Mail_Group extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->logic	= new Logic_Mail_Group( $this->env );
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
/*		if( $this->request->has( 'save' ) ){
			$this->logic->removeMember( $groupId, $memberId );
		}*/
		$groups	= $this->logic->getActiveGroups();
		$this->addData( 'groups', $groups );
	}
}
