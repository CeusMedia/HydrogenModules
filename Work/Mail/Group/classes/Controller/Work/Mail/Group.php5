<?php
class Controller_Work_Mail_Group extends CMF_Hydrogen_Controller{

	protected $modelGroup;
	protected $modelMember;
	protected $messenger;
	protected $request;
	protected $session;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->modelGroup	= new Model_Mail_Group( $this->env );
		$this->modelMember	= new Model_Mail_Group_Member( $this->env );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
//			...
			$this->messenger->noteSuccess( 'Added.' );
		}
//		$this->addData( 'servers', $this->modelServer->getAll() );
	}

	protected function checkGroupId( $id, $strict = TRUE ){
		if( $this->modelGroup->has( $id ) )
			return $this->modelGroup->get( $id );
		if( $strict )
			throw new RuntimeException( 'Invalid Mail Group ID' );
	}

	public function edit( $id ){
		$group	= $this->checkGroupId( $id );
		if( $this->request->has( 'save' ) ){
//			...
			$this->messenger->noteSuccess( 'Saved.' );
		}
		$this->addData( 'group', $group );
	}

	public function index(){
		$groups		= $this->modelGroup->getAll();
		foreach( $groups as $group )
			$group->members		= $this->modelMember->getAll(
				 array( 'mailGroupId' => $group->mailGroupId ),
				 array(),
				 array()
			);
		$this->addData( 'groups', $groups );
	}
}
