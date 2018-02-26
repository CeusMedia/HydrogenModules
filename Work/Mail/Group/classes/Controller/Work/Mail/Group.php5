<?php
class Controller_Work_Mail_Group extends CMF_Hydrogen_Controller{

	protected $request;
	protected $session;
	protected $messenger;
	protected $modelGroup;
	protected $modelMember;
	protected $modelRole;
	protected $modelServer;
	protected $modelAction;
	protected $logic;
	protected $logicMail;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->modelGroup	= new Model_Mail_Group( $this->env );
		$this->modelMember	= new Model_Mail_Group_Member( $this->env );
		$this->modelRole	= new Model_Mail_Group_Role( $this->env );
		$this->modelAction	= new Model_Mail_Group_Action( $this->env );
//		$this->modelServer	= new Model_Mail_Group_Server( $this->env );
		$this->modelUser	= new Model_User( $this->env );
		$this->logic		= new Logic_Mail_Group( $this->env );
		$this->logicMail	= new Logic_Mail( $this->env );
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
//		$this->addData( 'servers', $this->modelServer->getAll() );
		$users		= $this->modelUser->getAll( array( 'status' => '>0' ), array( 'username' => 'ASC' ) );
		$this->addData( 'users', $users );
		$roles		= $this->modelRole->getAll( array(), array( 'rank' => 'ASC' ) );
		$this->addData( 'roles', $roles );
	}

	public function addMember( $groupId ){
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
			$action	= $this->logic->registerMemberAction( 'informAfterFirstActivate', $groupId, $memberId, '' );
		if( $invite ){
			$action	= $this->logic->registerMemberAction( 'confirmAfterJoin', $groupId, $memberId, '' );
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

	protected function checkGroupId( $groupId, $strict = TRUE ){
		if( $this->modelGroup->has( $groupId ) )
			return $this->modelGroup->get( $groupId );
		if( $strict )
			throw new RuntimeException( 'Invalid Mail Group ID' );
	}

	protected function checkMemberId( $memberId, $strict = TRUE ){
		$member	= $this->modelMember->get( $memberId );
		if( $member )
			return $member;
		if( $strict )
			throw new RangeException( 'Invalid member ID: '.$memberId );
		return NULL;
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

	public function removeMember( $mailGroupId, $mailGroupMemberId ){
		$member	= $this->checkMemberId( $mailGroupMemberId );
		$this->modelMember->remove( $mailGroupMemberId );
		$this->restart( 'edit/'.$mailGroupId, TRUE );
	}

	protected function setMemberStatusToActivated( $group, $member ){
		$mailData	= array(
			'group'		=> $group,
			'member'	=> $member,
		);
		if( $group->type == Model_Mail_Group::TYPE_REGISTER ){
			if( $member->status == Model_Mail_Group_Member::STATUS_CONFIRMED ){
				$action	= $this->modelAction->getByIndices( array(
					'mailGroupId'		=> $group->mailGroupId,
					'mailGroupMemberId'	=> $member->mailGroupMemberId,
					'action'			=> 'confirmAfterJoin',
					'status'			=> Model_Mail_Group_Action::STATUS_HANDLED,
				) );
				if( $action ){
					$this->modelAction->add( array(
						'mailGroupId'		=> $group->mailGroupId,
						'mailGroupMemberId'	=> $member->mailGroupMemberId,
						'uuid'				=> Alg_ID::uuid(),
						'action'			=> 'informAfterFirstActivate',
						'message'			=> $action->message,
						'createdAt'			=> time(),
						'modifiedAt'		=> time(),
					) );
					$this->modelMember->edit( $member->mailGroupMemberId, array(
						'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
						'modifiedAt'	=> time(),
					) );
					$mail		= new Mail_Info_Mail_Group_Activated( $this->env, $mailData );
					$receiver	= (object) array(
						'username'	=> $member->title,
						'email'		=> $member->address
					);
					$language	= $this->env->getLanguage()->getLanguage();
					$this->logicMail->appendRegisteredAttachments( $mail, $language );
					$this->logicMail->handleMail( $mail, $receiver, $language );
					return TRUE;
				}
			}
		}
		$this->modelMember->edit( $member->mailGroupMemberId, array(
			'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
			'modifiedAt'	=> time(),
		) );
		$mail		= new Mail_Info_Mail_Group_Member_Activated( $this->env, $mailData );
		$receiver	= (object) array(
			'username'	=> $member->title,
			'email'		=> $member->address
		);
		$language	= $this->env->getLanguage()->getLanguage();
		$this->logicMail->appendRegisteredAttachments( $mail, $language );
		$this->logicMail->handleMail( $mail, $receiver, $language );

		$manager	= $this->modelUser->get( $group->managerId );
		$members	= $this->modelMember->getAllByIndices( array(
			'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
			'mailGroupId'	=> $group->mailGroupId,
		) );
		foreach( $members as $entry ){
			if( $entry->address == $manager->email )
				continue;
			if( $entry->mailGroupMemberId == $member->mailGroupMemberId )
				continue;
			$mail		= new Mail_Info_Mail_Group_Members_MemberActivated( $this->env, $mailData );
			$receiver	= (object) array(
				'username'	=> $entry->title,
				'email'		=> $entry->address
			);
			$language	= $this->env->getLanguage()->getLanguage();
			$this->logicMail->handleMail( $mail, $receiver, $language );
		}
	}

	protected function setMemberStatusToDeactivated( $group, $member ){
		$mailData	= array(
			'group'		=> $group,
			'member'	=> $member,
		);
		if( $group->type == Model_Mail_Group::TYPE_REGISTER ){
			if( $member->status == Model_Mail_Group_Member::STATUS_CONFIRMED ){
				$this->modelMember->edit( $member->mailGroupMemberId, array(
					'status'		=> Model_Mail_Group_Member::STATUS_DEACTIVATED,
					'modifiedAt'	=> time(),
				) );
				$mail		= new Mail_Info_Mail_Group_Deactivated( $this->env, $mailData );
				$receiver	= (object) array(
					'username'	=> $member->title,
					'email'		=> $member->address
				);
				$language	= $this->env->getLanguage()->getLanguage();
				$this->logicMail->appendRegisteredAttachments( $mail, $language );
				$this->logicMail->handleMail( $mail, $receiver, $language );
				return TRUE;
			}
		}
		$this->modelMember->edit( $member->mailGroupMemberId, array(
			'status'		=> Model_Mail_Group_Member::STATUS_DEACTIVATED,
			'modifiedAt'	=> time(),
		) );
		$mail		= new Mail_Info_Mail_Group_Member_Deactivated( $this->env, $mailData );
		$receiver	= (object) array(
			'username'	=> $member->title,
			'email'		=> $member->address
		);
		$language	= $this->env->getLanguage()->getLanguage();
		$this->logicMail->appendRegisteredAttachments( $mail, $language );
		$this->logicMail->handleMail( $mail, $receiver, $language );

		$manager	= $this->modelUser->get( $group->managerId );
		$members	= $this->modelMember->getAllByIndices( array(
			'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
			'mailGroupId'	=> $group->mailGroupId,
		) );
		foreach( $members as $entry ){
			if( $entry->address == $manager->email )
				continue;
			if( $entry->mailGroupMemberId == $member->mailGroupMemberId )
				continue;
			$mail		= new Mail_Info_Mail_Group_Members_MemberDeactivated( $this->env, $mailData );
			$receiver	= (object) array(
				'username'	=> $entry->title,
				'email'		=> $entry->address
			);
			$language	= $this->env->getLanguage()->getLanguage();
			$this->logicMail->handleMail( $mail, $receiver, $language );
		}
	}

	public function setMemberStatus( $mailGroupId, $mailGroupMemberId, $status ){
		$member	= $this->checkMemberId( $mailGroupMemberId );
		if( (int) $member->status !== (int) $status ){
			$group		= $this->checkGroupId( $mailGroupId );
			$mailData	= array(
				'group'		=> $group,
				'member'	=> $member,
			);
			if( $status == Model_Mail_Group_Member::STATUS_ACTIVATED ){
				$this->setMemberStatusToActivated( $group, $member );
				$this->messenger->noteSuccess( 'Das Mitglied wurde aktiviert und darüber informiert.' );

			}
			else if( $status == Model_Mail_Group_Member::STATUS_DEACTIVATED ){
				$this->setMemberStatusToDeactivated( $group, $member );
				$this->messenger->noteSuccess( 'Das Mitglied wurde deaktiviert und darüber informiert.' );
			}
		}
		$this->restart( 'edit/'.$mailGroupId, TRUE );
	}
}
