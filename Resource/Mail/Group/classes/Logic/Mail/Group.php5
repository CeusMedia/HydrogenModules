<?php
class Logic_Mail_Group extends CMF_Hydrogen_Logic{

	protected $modelGroup;
	protected $modelMember;
	protected $modelMessage;
	protected $modelRole;
	protected $modelServer;
	protected $modelAction;
	protected $modelUser;
	protected $logicMail;
	protected $transports		= array();

	public function __onInit(){
		$this->modelGroup	= new Model_Mail_Group( $this->env );
		$this->modelMember	= new Model_Mail_Group_Member( $this->env );
		$this->modelMessage	= new Model_Mail_Group_Message( $this->env );
		$this->modelRole	= new Model_Mail_Group_Role( $this->env );
		$this->modelServer	= new Model_Mail_Group_Server( $this->env );
		$this->modelAction	= new Model_Mail_Group_Action( $this->env );
		$this->modelUser	= new Model_User( $this->env );
		$this->logicMail	= Logic_Mail::getInstance( $this->env );
	}

	public function addGroup( $data ){
		$data		= array_merge( array(
			"defaultRoleId"			=> 1,
			"managerId"				=> 0,
			"type"					=> Model_Mail_Group::TYPE_REGISTER,
			"visibility"			=> Model_Mail_Group::VISIBILITY_INSIDE,
			"status"				=> Model_Mail_Group::STATUS_NEW,
			"title"					=> NULL,
			"address"				=> NULL,
			"password"				=> NULL,
			"bounce"				=> NULL,
			"subtitle"				=> NULL,
			"description"			=> NULL,
		), $data, array(
			'createdAt'				=> time(),
			'modifiedAt'			=> time(),
		) );
		if( !strlen( trim( $data['address'] ) ) )
			throw new InvalidArgumentException( 'No mailbox address given' );
		if( !strlen( trim( $data['password'] ) ) )
			throw new InvalidArgumentException( 'No mailbox password given' );
		if( !strlen( trim( $data['title'] ) ) )
			throw new InvalidArgumentException( 'No title' );
		$groupId	= $this->modelGroup->add( $data );
		$this->createGroupMailAccountUsingPlesk( $groupId );
		return $groupId;
	}

	public function addGroupMember( $groupId, $address, $title ){
		$group	= $this->checkGroupId( $groupId );
		$member	= $this->getGroupMemberByAddress( $groupId, $address, FALSE, FALSE );
		if( $member )
			return $member->mailGroupMemberId;
		$groupMemberId	= $this->modelMember->add( array(
			'mailGroupId'	=> $groupId,
			'roleId'		=> $group->defaultRoleId,
			'status'		=> Model_Mail_Group_Member::STATUS_REGISTERED,
			'address'		=> $address,
			'title'			=> $title,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		) );
		return $groupMemberId;
	}

/*	public function addGroupMember( $groupId, $memberId ){
		$group	= $this->checkGroupId( $groupId );
		if( $this->isGroupMember( $groupId, $memberId ) )
			return;
		$member	= $this->checkMemberId( $memberId );
		$groupMemberId	= $this->modelMember->add( array(
			'mailGroupId'	=> $groupId,
			'roleId'		=> $group->defaultRoleId,
			'status'		=> Model_Mail_Group_Member::STATUS_REGISTERED,
			'address'		=> $member->address,
			'title'			=> $member->title,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		) );
		return $groupMemberId;
	}*/

	public function autojoinMemberByMessage( $groupId, $message ){
		$allowedGroupStatuses	= array(
			Model_Mail_Group::STATUS_ACTIVATED,
 			Model_Mail_Group::STATUS_WORKING,
		);
		$group	= $this->getGroup( $message->mailGroupId );
		if( !$group )
			throw new RuntimeException( 'Invalid group ID' );
		if( !in_array( $group->status, $allowedGroupStatuses ) )
			throw new RuntimeException( 'Group is not activated' );
		if( (int) $group->type !== Model_Mail_Group::TYPE_AUTOJOIN )
			throw new RuntimeException( 'Group type is not AUTOJOIN' );

		$mail			= $this->env->logic->mailGroupMessage->getMessageObject( $message );
		$senderAddress	= $mail->getSender()->getAddress();
		$senderName		= $mail->getSender()->getName();
		if( !$senderName )
			$senderName	= $mail->getSender()->getLocalPart();
		$senderMember	= $this->getGroupMemberByAddress( $groupId, $senderAddress, FALSE, FALSE );
		if( !$senderMember ){
			$senderMemberId	= $this->addGroupMember(
 				$groupId,
				$senderAddress,
				$senderName
			);
			$senderMember	= $this->checkMemberId( $senderMemberId );
			$this->modelMessage->edit( $message->mailGroupMessageId, array(
				'mailGroupMemberId'	=> $senderMember->mailGroupMemberId,
			) );
		}
//		$action		= $this->registerMemberAction( 'confirmAfterJoin', $groupId, $senderMember->mailGroupMemberId, '' );
		$mailData	= array(
			'member'	=> $senderMember,
			'group'		=> $group,
//			'action'	=> $action,
		);
		$receiver	= (object) array(
			'username'	=> $senderMember->title,
			'email'		=> $senderMember->address,
		);
		$this->logicMail->handleMail(
			new Mail_Info_Mail_Group_Autojoined( $this->env, $mailData ),
			$receiver,
			$this->env->getLanguage()->getLanguage()
		);
		return $senderMember->mailGroupMemberId;
	}

	/**
	 *	Check mail group by ID.
	 *	Alias for checkGroupId.
	 *	@access		public
	 *	@param		integer			$groupId		...
	 *	@param		boolean			$strict			Flag: throw exception if not existing
	 *	@return		object			Group model object if existing
	 *	@throws		RangeException	if mail group is not existing
	 *	@todo 		make this the main implementation after extraction of this large logic to sub logic classes.
	 */
	public function checkId( $groupId, $strict = TRUE ){
		return $this->checkGroupId( $groupId, $strict );
	}

	/**
	 *	Check mail group by ID.
	 *	@access		public
	 *	@param		integer			$groupId		...
	 *	@param		boolean			$strict			Flag: throw exception if not existing
	 *	@return		object			Group model object if existing
	 *	@throws		RangeException	if mail group is not existing
	 */
	public function checkGroupId( $groupId, $strict = TRUE ){
		$group	= $this->modelGroup->get( $groupId );
		if( $group )
			return $group;
		if( $strict )
			throw new RangeException( 'Invalid group ID: '.$groupId );
		return NULL;
	}

	public function checkMemberId( $memberId, $strict = TRUE ){
		$member	= $this->modelMember->get( $memberId );
		if( $member )
			return $member;
		if( $strict )
			throw new RangeException( 'Invalid member ID: '.$memberId );
		return NULL;
	}

	public function checkServerId( $serverId, $strict = TRUE ){
		$server	= $this->modelServer->get( $serverId );
		if( $server )
			return $server;
		if( $strict )
			throw new RangeException( 'Invalid server ID: '.$serverId );
		return NULL;
	}

	public function countGroupMembers( $groupId, $activeOnly = FALSE ){
		$indices	= array( 'mailGroupId' => $groupId );
		if( $activeOnly )
			$indices['status']	= Model_Mail_Group_Member::STATUS_ACTIVATED;
		return $this->modelMember->count( $indices );
	}

	public function countGroupMessages( $groupId, $forwardedOnly = FALSE ){
		$indices	= array( 'mailGroupId' => $groupId );
		if( $forwardedOnly )
			$indices['status']	= Model_Mail_Group_Message::STATUS_FORWARDED;
		return $this->modelMessage->count( $indices );
	}

	/**
	 *	Tries to create a mailbox usind Plesk command line utilities
	 *	@see		https://docs.plesk.com/en-US/onyx/cli-linux/using-command-line-utilities/mail-mail-accounts.39181/
	 *	@todo		finish impl (find a way to execute command as root), run checks beforehand
	 */
	protected function createGroupMailAccountUsingPlesk( $groupId ){
		$group			= $this->checkGroupId( $mailGroupId );
		if( $group->status !== Model_Mail_Group::STATUS_NEW )
			throw new RuntimeException( 'Mail group be in status STATUS_NEW' );
		$options		= array(
			'--create '.$group->address,
			'--passwd '.$group->password,
			'-mailbox true',
			'-mbox_quota 50M',
			'-antivirus inout',
			'-description "'.$group->title.'"',
		);
		$command		= 'plesk bin mail '.join( ' ', $options );

	//	@todo: find a way to execute command as root
		error_log( $command.PHP_EOL, 3, 'commands.log' );
		$this->modelGroup->edit( $groupId, array( 'status' => Model_Mail_Group::STATUS_EXISTING ) );
	}

	public function getActiveGroups(){
		return $this->getGroups( TRUE );
	}

	public function getGroup( $groupId, $activeOnly = FALSE, $strict = TRUE ){
		$indices	= array( 'mailGroupId' => $groupId );
		if( $activeOnly )
			$indices['status']	= array(
				Model_Mail_Group::STATUS_ACTIVATED,
				Model_Mail_Group::STATUS_WORKING,
			);
		if( ( $group = $this->modelGroup->getByIndices( $indices ) ) )
			return $group;
		if( !$strict )
			return NULL;
		throw new RangeException( 'Invalid group ID: '.$groupId );
	}

	public function getGroups( $activeOnly = FALSE ){
		$indices	= array();
		if( $activeOnly )
			$indices['status']	= array(
				Model_Mail_Group::STATUS_ACTIVATED,
				Model_Mail_Group::STATUS_WORKING,
			);
		$list	= array();
		foreach( $this->modelGroup->getAll( $indices ) as $group )
			$list[$group->mailGroupId]	= $group;
		return $list;
	}

	public function getGroupMember( $memberId, $activeOnly = FALSE ){
		$indices	= array( 'mailGroupMemberId' => $memberId );
		if( $activeOnly )
			$indices['status']	= Model_Mail_Group_Member::STATUS_ACTIVATED;
		return $this->modelMember->getByIndices( $indices );
	}

	public function getGroupMembers( $groupId, $activeOnly = FALSE ){
		$indices	= array( 'mailGroupId' => $groupId );
		if( $activeOnly )
			$indices['status']	= Model_Mail_Group_Member::STATUS_ACTIVATED;
		return $this->modelMember->getAllByIndices( $indices );
	}

	public function getGroupMemberByAddress( $groupId, $address, $activeOnly = FALSE, $strict = TRUE ){
		$indices	= array( 'mailGroupId' => $groupId, 'address' => $address );
		if( $activeOnly )
			$indices['status']	= Model_Mail_Group_Member::STATUS_ACTIVATED;
		if( ( $member = $this->modelMember->getByIndices( $indices ) ) )
			return $member;
		if( !$strict )
			return NULL;
		$group	= $this->getGroup( $groupId );
		$msg	= 'Member address &lt;%s&gt; is not in group "%s"';
		throw new RangeException( sprintf( $msg, $address, $group->title ) );
	}

	protected function getMailbox( $groupId ){
		$group		= $this->checkGroupId( $groupId );
		$server		= $this->modelServer->get( $group->mailGroupServerId );

		$flags		= array( 'imap' );
		if( (int) $server->imapPort === 993 )
			$flags[]	= 'ssl';
		else if( (int) $server->imapPort === 143 )
			$flags[]	= 'tls';
		$flags		= join( '/', $flags );
		$mailbox	= new \PhpImap\Mailbox(
			sprintf( '{%s:%d/%s}INBOX', $server->imapHost, $server->imapPort, $flags ),
			$group->address,
			$group->password,
			'data/attachments'
		);
		$mailbox->setExpungeOnDisconnect( TRUE );
		return $mailbox;
	}

	public function getMailGroupFromAddress( $address, $activeOnly = FALSE, $strict = TRUE ){
		$indices	= array( 'address' => $address );
		if( $activeOnly )
			$indices['status']	= array(
				Model_Mail_Group::STATUS_ACTIVATED,
				Model_Mail_Group::STATUS_WORKING,
			);
		if( ( $group = $this->modelGroup->getByIndices( $indices ) ) )
			return $group;
		if( !$strict )
			return NULL;
		throw new RangeException( sprintf(
			'No%s mail group found by %s',
			$activeOnly ? ' active' : '',
 			$address
		) );
	}

	public function getMemberByAddress( $address, $activeOnly = FALSE, $strict = TRUE ){
		$indices	= array( 'address' => $address );
		if( $activeOnly )
			$indices['status']	= Model_Mail_Group_Member::STATUS_ACTIVATED;
		if( ( $member = $this->modelMember->getByIndices( $indices ) ) )
			return $member;
		if( !$strict )
			return NULL;
	}

	/**
	 *	Reads mailbox to find unseen mails and imports them with status STATUS_NEW.
	 *	Imported messages need to be handled afterwards by handleImportedGroupMessages.
	 *	@access		public
	 *	@param		integer		$groupId		Group ID
	 *	@param		boolean		$dry			Flag: Dry mode (default: no)
	 *	@return		array		list of resulting message ids or import error
	 */
	public function importGroupMails( $groupId, $dry = FALSE ){
		$results	= (object) array(
			'mailsImported'	=> array(),
			'errors'		=> array(),
		);
		$mailbox	= $this->getMailbox( $groupId );
		$mailIds	= $mailbox->searchMailbox( 'UNSEEN' );
//		$mailIds	= $limit > 0 ? array_slice( $mailIds, 0, $limit ) : $mailIds;
		foreach( $mailIds as $mailId ){
			$mail		= $mailbox->getRawMail( $mailId, FALSE );
			try{
				$messageId	= 0;
				if( !$dry ){
					$messageId	= $this->env->logic->mailGroupMessage->addFromRawMail( $groupId, $mail );
					$mailbox->markMailAsRead( $mailId );
				}
				$results->mailsImported[]	= $messageId;
			}
			catch( Exception $e ){
				$results->errors[]	= $e->getMessage();
			}
		}
		return $results;
	}

	public function isGroupMember( $groupId, $memberId ){
		return (bool) $this->modelMember->count( array(
			'mailGroupMemberId'		=> $memberId,
			'mailGroupId'			=> $groupId,
		) );
	}

	public function isGroupMemberAddress( $groupId, $address ){
		return (bool) $this->modelMember->count( array(
			'mailGroupId'		=> $groupId,
			'address'			=> $address,
		) );
	}

	public function registerMemberAction( $action, $groupId, $memberId, $message ){
		$actionId	= $this->modelAction->add( array(
			'mailGroupId'		=> $groupId,
			'mailGroupMemberId'	=> $memberId,
			'status'			=> Model_Mail_Group_Action::STATUS_REGISTERED,
			'uuid'				=> Alg_ID::uuid(),
			'action'			=> $action,
			'message'			=> $message,
			'createdAt'			=> time(),
			'modifiedAt'		=> time(),
		) );
		return $this->modelAction->get( $actionId );
	}

	public function setGroupBounce( $groupId, $bounce ){
		$data		= array( 'bounce' => $bounce );
		$this->updateGroup( $groupId, $data, __METHOD__ );
	}

	public function setGroupStatus( $groupId, $status ){
		$data		= array( 'status' => $status );
		$this->updateGroup( $groupId, $data, __METHOD__ );
	}

	public function setGroupTitle( $groupId, $title ){
		$data		= array( 'title' => $title );
		$this->updateGroup( $groupId, $data, __METHOD__ );
	}

	public function setGroupType( $groupId, $type ){
		$data		= array( 'type' => $type );
		$this->updateGroup( $groupId, $data, __METHOD__ );
	}

	public function setGroupVisibility( $groupId, $visibility ){
		$data		= array( 'visibility' => $visibility );
		$this->updateGroup( $groupId, $data, __METHOD__ );
	}


	public function setMemberStatus( $groupId, $memberId, $status ){
		$member	= $this->checkMemberId( $memberId );
		if( (int) $member->status === (int) $status )
			return FALSE;
		$group		= $this->checkGroupId( $groupId );
		$mailData	= array(
			'group'		=> $group,
			'member'	=> $member,
		);
		switch( $status ){
			case Model_Mail_Group_Member::STATUS_ACTIVATED;
				$this->setMemberStatusToActivated( $group, $member );
				break;
			case Model_Mail_Group_Member::STATUS_DEACTIVATED;
				$this->setMemberStatusToDeactivated( $group, $member );
				break;
			case Model_Mail_Group_Member::STATUS_REJECTED;
				$this->setMemberStatusToRejected( $group, $member );
				break;
		}
		return TRUE;
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
				}
			}
		}
		$this->modelMember->edit( $member->mailGroupMemberId, array(
			'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
			'modifiedAt'	=> time(),
		) );
		$this->env->getCaptain()->callHook( 'MailGroup', 'memberActivated', $this, array(
			'group'			=> $group,
			'member'		=> $this->modelMember->get( $member->mailGroupMemberId ),
			'informMembers'	=> TRUE,
		) );
	}

	protected function setMemberStatusToDeactivated( $group, $member ){
		$mailData	= array(
			'group'		=> $group,
			'member'	=> $member,
		);
		$memberWasActive	= TRUE;
		if( $group->type == Model_Mail_Group::TYPE_REGISTER ){
			if( $member->status == Model_Mail_Group_Member::STATUS_CONFIRMED ){
				$memberWasActive	= FALSE;
			}
		}
		$this->modelMember->edit( $member->mailGroupMemberId, array(
			'status'		=> Model_Mail_Group_Member::STATUS_DEACTIVATED,
			'modifiedAt'	=> time(),
		) );
		$this->env->getCaptain()->callHook( 'MailGroup', 'memberDeactivated', $this, array(
			'group'			=> $group,
			'member'		=> $this->modelMember->get( $member->mailGroupMemberId ),
			'informMembers'	=> $memberWasActive,
		) );
	}

	protected function setMemberStatusToRejected( $group, $member ){
		$mailData	= array(
			'group'		=> $group,
			'member'	=> $member,
		);
		$this->modelMember->edit( $member->mailGroupMemberId, array(
			'status'		=> Model_Mail_Group_Member::STATUS_REJECTED,
			'modifiedAt'	=> time(),
		) );
		$this->env->getCaptain()->callHook( 'MailGroup', 'memberRejected', $this, array(
			'group'			=> $group,
			'member'		=> $this->modelMember->get( $member->mailGroupMemberId ),
		) );
	}


	public function testGestMail( $groupId, $limit = 1 ){
		return;
	}

	//  --  PROTECTED METHODS --  //

	protected function updateGroup( $groupId, $data, $method = NULL ){
		$group		= $this->checkGroupId( $groupId );
		$this->modelGroup->edit( $groupId, $data );
		return $this->env->getCaptain()->callHook( 'MailGroup', 'change', $this, array(
			'groupId'		=> $groupId,
			'before'		=> $group,
			'changes'		=> $data,
			'method'		=> $method,
		) );
	}
}
?>
