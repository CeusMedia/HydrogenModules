<?php
class Controller_Work_Mail_Group extends CMF_Hydrogen_Controller{

	protected $modelGroup;
	protected $modelMember;
	protected $modelRole;
	protected $modelServer;
	protected $messenger;
	protected $request;
	protected $session;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->modelGroup	= new Model_Mail_Group( $this->env );
		$this->modelMember	= new Model_Mail_Group_Member( $this->env );
		$this->modelRole	= new Model_Mail_Group_Role( $this->env );
//		$this->modelServer	= new Model_Mail_Group_Server( $this->env );
		$this->modelUser		= new Model_User( $this->env );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$title		= trim( $this->request->get( 'title' ) );
			$address	= trim( $this->request->get( 'address' ) );
			if( $this->modelGroup->getByIndex( 'title', $title ) ){
				$this->messenger->noteError( 'Title "%s" is already existing.' );
				$this->restart( 'add', TRUE );
			}
			if( $this->modelGroup->getByIndex( 'address', $address ) ){
				$this->messenger->noteError( 'Address "%s" is already existing.' );
				$this->restart( 'add', TRUE );
			}
			$data		= array(
				'mailGroupServerId'	=> 1,
				'status'			=> $this->request->get( 'status' ),
				'adminId'			=> $this->request->get( 'adminId' ),
				'title'				=> $title,
				'address'			=> $address,
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			);
			$groupId	= $this->modelGroup->add( $data );
			$this->messenger->noteSuccess( 'Added.' );
			$this->restart( 'edit/'.$groupId, TRUE );
		}
//		$this->addData( 'servers', $this->modelServer->getAll() );
		$users		= $this->modelUser->getAll( array( 'status' => '>0' ), array( 'username' => 'ASC' ) );
		$this->addData( 'users', $users );
		$roles		= $this->modelRole->getAll( array(), array( 'rank' => 'ASC' ) );
		$this->addData( 'roles', $roles );
	}

	public function addMember( $groupId ){
		$title		= $this->request->get( 'title' );
		$address	= $this->request->get( 'address' );
		$this->modelMember->add( array(
			'mailGroupId'	=> $groupId,
			'title'			=> $title,
			'address'		=> $address,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		) );
		$this->restart( 'edit/'.$groupId, TRUE );
	}

	protected function checkGroupId( $groupId, $strict = TRUE ){
		if( $this->modelGroup->has( $groupId ) )
			return $this->modelGroup->get( $groupId );
		if( $strict )
			throw new RuntimeException( 'Invalid Mail Group ID' );
	}

	public function edit( $groupId ){
		$group	= $this->checkGroupId( $groupId );
		if( $this->request->has( 'save' ) ){
			$title		= trim( $this->request->get( 'title' ) );
			$address	= trim( $this->request->get( 'address' ) );
			if( $this->modelGroup->getAll( array( 'title' => $title, 'mailGroupId' => '!='.$groupId ) ) ){
				$this->messenger->noteError( 'Title "%s" is already existing.' );
				$this->restart( 'edit/'.$groupId, TRUE );
			}
			if( $this->modelGroup->getAll( array( 'address' => $address, 'mailGroupId' => '!='.$groupId ) ) ){
				$this->messenger->noteError( 'Address "%s" is already existing.' );
				$this->restart( 'edit/'.$groupId, TRUE );
			}
			$groupId	= $this->modelGroup->edit( $groupId, array(
				'mailServerId'	=> 1,
				'status'		=> $this->request->get( 'status' ),
				'adminId'		=> $this->request->get( 'adminId' ),
				'title'			=> $title,
				'address'		=> $address,
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			) );
			$this->messenger->noteSuccess( 'Saved.' );
			$this->restart( 'edit/'.$groupId );
		}
		$this->addData( 'group', $group );
		$users		= $this->modelUser->getAll( array( 'status' => '>0' ), array( 'username' => 'ASC' ) );
		$this->addData( 'users', $users );
		$members	= $this->modelMember->getAll( array( 'mailGroupId' => $groupId ), array( 'title' => 'ASC' ) );
		$this->addData( 'members', $members );
		$roles		= $this->modelRole->getAll( array(), array( 'rank' => 'ASC' ) );
		$this->addData( 'roles', $roles );
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

	protected function checkMemberId( $memberId, $strict = TRUE ){
		$member	= $this->modelMember->get( $memberId );
		if( $member )
			return $member;
		if( $strict )
			throw new RangeException( 'Invalid member ID: '.$memberId );
		return NULL;
	}

	public function removeMember( $mailGroupId, $mailGroupMemberId ){
		$member	= $this->checkMemberId( $mailGroupMemberId );
		$this->modelMember->remove( $mailGroupId );
		$this->restart( 'edit/'.$mailGroupId, TRUE );
	}

	public function setMemberStatus( $mailGroupId, $mailGroupMemberId, $status ){
		$member	= $this->checkMemberId( $mailGroupMemberId );
		if( (int) $member->status !== (int) $status ){
			$this->modelMember->edit( $mailGroupMemberId, array(
				'status'		=> (int) $status,
				'modifiedAt'	=> time(),
			) );
			switch( (int) $status ){
				case 1:
					break;
				case -1:
					break;
			}
		}
		$this->restart( 'edit/'.$mailGroupId, TRUE );
	}
}
