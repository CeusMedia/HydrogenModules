<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Mail_Group extends Controller
{
	protected $request;
	protected $session;
	protected $messenger;
	protected $modelGroup;
	protected $modelMember;
	protected $modelRole;
	protected $modelServer;
	protected $modelAction;
	protected $modelUser;
	protected $logicGroup;
	protected $logicMail;

	public function add()
	{
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
				'defaultRoleId'		=> $this->request->get( 'roleId' ),
				'status'			=> $this->request->get( 'status' ),
				'managerId'			=> $this->request->get( 'managerId' ),
				'type'				=> $this->request->get( 'type' ),
				'visibility'		=> $this->request->get( 'visibility' ),
				'title'				=> $title,
				'address'			=> $address,
				'password'			=> trim( $this->request->get( 'password' ) ),
				'description'		=> $this->request->get( 'description' ),
				'bounce'			=> '',
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			);
			$groupId	= $this->modelGroup->add( $data );
			$this->messenger->noteSuccess( 'Added.' );
			$this->restart( 'edit/'.$groupId, TRUE );
		}
		$this->addData( 'servers', $this->modelServer->getAll() );
		$users		= $this->modelUser->getAll( ['status' => '> 0'], ['username' => 'ASC'] );
		$this->addData( 'users', $users );
		$roles		= $this->modelRole->getAll( [], ['rank' => 'ASC'] );
		$this->addData( 'roles', $roles );
	}

	public function addMember( $groupId )
	{
		$title		= $this->request->get( 'title' );
		$address	= $this->request->get( 'address' );

		$invite		= $this->request->has( 'invite' );
		$quiet		= $this->request->has( 'quiet' );

		$status		= Model_Mail_Group_Member::STATUS_ACTIVATED;
		if( $invite )
			$status		= Model_Mail_Group_Member::STATUS_REGISTERED;

		$memberId	= $this->modelMember->add( array(
			'mailGroupId'	=> $groupId,
			'roleId'		=> $this->request->get( 'roleId' ),
			'status'		=> $status,				//$this->request->get( 'status' ),
			'title'			=> $title,
			'address'		=> $address,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		) );
		if( !$quiet )
			$action	= $this->logicGroup->registerMemberAction( 'informAfterFirstActivate', $groupId, $memberId, '' );
		if( $invite ){
			$action	= $this->logicGroup->registerMemberAction( 'confirmAfterJoin', $groupId, $memberId, '' );
			$mailData	= array(
				'group'		=> $this->checkGroupId( $groupId ),
				'member'	=> $this->modelMember->get( $memberId ),
				'action'	=> $action,
			);
			$mail		= new Mail_Info_Mail_Group_Member_Invited( $this->env, $mailData );
		}
		else{
			$mailData	= array(
				'group'		=> $this->checkGroupId( $groupId ),
				'member'	=> $this->modelMember->get( $memberId ),
			);
			$mail		= new Mail_Info_Mail_Group_Member_Added( $this->env, $mailData );
		}
		$receiver	= (object) array(
			'username'	=> $title,
			'email'		=> $address
		);
		$language	= $this->env->getLanguage()->getLanguage();
		$this->logicMail->appendRegisteredAttachments( $mail, $language );
		$this->logicMail->handleMail( $mail, $receiver, $language );
		$this->restart( 'edit/'.$groupId, TRUE );
	}

	public function edit( $groupId )
	{
		$group	= $this->checkGroupId( $groupId );
		if( $this->request->has( 'save' ) ){
			$title		= trim( $this->request->get( 'title' ) );
			$address	= trim( $this->request->get( 'address' ) );
			if( $this->modelGroup->getAll( ['title' => $title, 'mailGroupId' => '!= '.$groupId] ) ){
				$this->messenger->noteError( 'Title "%s" is already existing.' );
				$this->restart( 'edit/'.$groupId, TRUE );
			}
			if( $this->modelGroup->getAll( ['address' => $address, 'mailGroupId' => '!= '.$groupId] ) ){
				$this->messenger->noteError( 'Address "%s" is already existing.' );
				$this->restart( 'edit/'.$groupId, TRUE );
			}
			$data		= array(
				'mailGroupServerId'	=> 1,
				'defaultRoleId'		=> $this->request->get( 'roleId' ),
				'status'			=> $this->request->get( 'status' ),
				'managerId'			=> $this->request->get( 'managerId' ),
				'type'				=> $this->request->get( 'type' ),
				'visibility'		=> $this->request->get( 'visibility' ),
				'title'				=> $title,
				'address'			=> $address,
				'description'		=> $this->request->get( 'description' ),
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			);
			if( strlen( trim( $this->request->get( 'password' ) ) ) )
				$data['password']	= trim( $this->request->get( 'password' ) );
			$this->modelGroup->edit( $groupId, $data );
			$this->messenger->noteSuccess( 'Saved.' );
			$this->restart( 'edit/'.$groupId, TRUE );
		}
		$this->addData( 'servers', $this->modelServer->getAll() );
		$this->addData( 'group', $group );
		$users		= $this->modelUser->getAll( ['status' => '> 0'], ['username' => 'ASC'] );
		$this->addData( 'users', $users );
		$members	= $this->modelMember->getAll( ['mailGroupId' => $groupId], ['title' => 'ASC'] );
		$this->addData( 'members', $members );
		$roles		= $this->modelRole->getAll( [], ['rank' => 'ASC'] );
		$this->addData( 'roles', $roles );
	}

	public function index()
	{
		$groups		= $this->modelGroup->getAll();
		foreach( $groups as $group )
			$group->members		= $this->modelMember->getAll(
				 ['mailGroupId' => $group->mailGroupId],
				 [],
				 []
			);
		$this->addData( 'groups', $groups );
	}

	public function removeMember( $mailGroupId, $mailGroupMemberId )
	{
		$member	= $this->checkMemberId( $mailGroupMemberId );
		$this->modelMember->remove( $mailGroupMemberId );
		$this->restart( 'edit/'.$mailGroupId, TRUE );
	}

	public function editMember( $mailGroupId, $mailGroupMemberId )
	{
		$member	= $this->checkMemberId( $mailGroupMemberId );
		$this->modelMember->edit( $mailGroupMemberId, array(
			'address'		=> $this->request->get( 'address' ),
			'title'			=> $this->request->get( 'title' ),
			'roleId'		=> $this->request->get( 'roleId' ),
			'modifiedAt'	=> time(),
		) );
		$this->restart( 'edit/'.$mailGroupId, TRUE );
	}

	public function setMemberStatus( $mailGroupId, $mailGroupMemberId, $status )
	{
		$group		= $this->checkGroupId( $mailGroupId );
		$member		= $this->checkMemberId( $mailGroupMemberId );
		$message	= '';
		if( $this->logicGroup->setMemberStatus( $mailGroupId, $mailGroupMemberId, $status ) ){
			if( $status == Model_Mail_Group_Member::STATUS_ACTIVATED )
				$message	= 'Das Mitglied wurde aktiviert und darüber informiert.';
			else if( $status == Model_Mail_Group_Member::STATUS_DEACTIVATED )
				$message	= 'Das Mitglied wurde deaktiviert und darüber informiert.';
			else if( $status == Model_Mail_Group_Member::STATUS_REJECTED )
				$message	= 'Das Mitglied wurde abgelehnt und darüber informiert.';
			if( $message )
				$this->messenger->noteSuccess( $message );
		}
		$this->restart( 'edit/'.$mailGroupId, TRUE );
	}

	protected function __onInit()
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();

		$this->modelGroup	= $this->getModel( 'mailGroup' );
		$this->modelMember	= $this->getModel( 'mailGroupMember' );
		$this->modelRole	= $this->getModel( 'mailGroupRole' );
		$this->modelAction	= $this->getModel( 'mailGroupAction' );
		$this->modelServer	= $this->getModel( 'mailGroupServer' );
		$this->modelGroup	= $this->getModel( 'mailGroup' );
		$this->modelUser	= $this->getModel( 'user' );
		$this->logicGroup	= $this->getLogic( 'mailGroup' );
		$this->logicMail	= $this->getLogic( 'mail' );
	}

	protected function checkGroupId( $groupId, bool $strict = TRUE )
	{
		return $this->logicGroup->checkGroupId( $groupId, $strict );
	}

	protected function checkMemberId( $memberId, bool $strict = TRUE )
	{
		return $this->logicGroup->checkMemberId( $memberId, $strict );
	}
}
