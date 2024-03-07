<?php

use CeusMedia\Common\Alg\ID;
use CeusMedia\HydrogenFramework\Logic;
use PhpImap\Mailbox as PhpImapMailbox;

class Logic_Mail_Group extends Logic
{
	protected Model_Mail_Group $modelGroup;
	protected Model_Mail_Group_Member $modelMember;
	protected Model_Mail_Group_Message $modelMessage;
	protected Model_Mail_Group_Role $modelRole;
	protected Model_Mail_Group_Server $modelServer;
	protected Model_Mail_Group_Action $modelAction;
	protected Model_User $modelUser;
	protected Logic_Mail $logicMail;

	public function addGroup( array $data ): string
	{
		$data		= array_merge( [
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
		], $data, [
			'createdAt'				=> time(),
			'modifiedAt'			=> time(),
		] );
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

	public function addGroupMember( int|string $groupId, string $address, string $title )
	{
		$group	= $this->checkGroupId( $groupId );
		$member	= $this->getGroupMemberByAddress( $groupId, $address, FALSE, FALSE );
		if( $member )
			return $member->mailGroupMemberId;
		return $this->modelMember->add( [
			'mailGroupId'	=> $groupId,
			'roleId'		=> $group->defaultRoleId,
			'status'		=> Model_Mail_Group_Member::STATUS_REGISTERED,
			'address'		=> $address,
			'title'			=> $title,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		] );
	}

/*	public function addGroupMember( int|string $groupId, int|string $memberId )
	{
		$group	= $this->checkGroupId( $groupId );
		if( $this->isGroupMember( $groupId, $memberId ) )
			return;
		$member	= $this->checkMemberId( $memberId );
		$groupMemberId	= $this->modelMember->add( [
			'mailGroupId'	=> $groupId,
			'roleId'		=> $group->defaultRoleId,
			'status'		=> Model_Mail_Group_Member::STATUS_REGISTERED,
			'address'		=> $member->address,
			'title'			=> $member->title,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		] );
		return $groupMemberId;
	}*/

	public function autojoinMemberByMessage( int|string $groupId, $message )
	{
		$allowedGroupStatuses	= [
			Model_Mail_Group::STATUS_ACTIVATED,
 			Model_Mail_Group::STATUS_WORKING,
		];
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
			$this->modelMessage->edit( $message->mailGroupMessageId, [
				'mailGroupMemberId'	=> $senderMember->mailGroupMemberId,
			] );
		}
//		$action		= $this->registerMemberAction( 'confirmAfterJoin', $groupId, $senderMember->mailGroupMemberId, '' );
		$mailData	= [
			'member'	=> $senderMember,
			'group'		=> $group,
//			'action'	=> $action,
		];
		$receiver	= (object) [
			'username'	=> $senderMember->title,
			'email'		=> $senderMember->address,
		];
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
	 *	@param		int|string		$groupId		...
	 *	@param		boolean			$strict			Flag: throw exception if not existing
	 *	@return		?object			Group model object if existing
	 *	@throws		RangeException	if mail group is not existing
	 *	@todo 		make this the main implementation after extraction of this large logic to sub logic classes.
	 */
	public function checkId( int|string $groupId, bool $strict = TRUE ): ?object
	{
		return $this->checkGroupId( $groupId, $strict );
	}

	/**
	 *	Check mail group by ID.
	 *	@access		public
	 *	@param		int|string		$groupId		...
	 *	@param		boolean			$strict			Flag: throw exception if not existing
	 *	@return		?object			Group model object if existing
	 *	@throws		RangeException	if mail group is not existing
	 */
	public function checkGroupId( int|string $groupId, bool $strict = TRUE ): ?object
	{
		$group	= $this->modelGroup->get( $groupId );
		if( $group )
			return $group;
		if( $strict )
			throw new RangeException( 'Invalid group ID: '.$groupId );
		return NULL;
	}

	public function checkMemberId( int|string $memberId, bool $strict = TRUE ): ?object
	{
		$member	= $this->modelMember->get( $memberId );
		if( $member )
			return $member;
		if( $strict )
			throw new RangeException( 'Invalid member ID: '.$memberId );
		return NULL;
	}

	public function checkServerId( int|string $serverId, bool $strict = TRUE ): ?object
	{
		$server	= $this->modelServer->get( $serverId );
		if( $server )
			return $server;
		if( $strict )
			throw new RangeException( 'Invalid server ID: '.$serverId );
		return NULL;
	}

	public function countGroupMembers( int|string $groupId, bool $activeOnly = FALSE ): int
	{
		$indices	= ['mailGroupId' => $groupId];
		if( $activeOnly )
			$indices['status']	= Model_Mail_Group_Member::STATUS_ACTIVATED;
		return $this->modelMember->count( $indices );
	}

	public function countGroupMessages( int|string $groupId, bool $forwardedOnly = FALSE ): int
	{
		$indices	= ['mailGroupId' => $groupId];
		if( $forwardedOnly )
			$indices['status']	= Model_Mail_Group_Message::STATUS_FORWARDED;
		return $this->modelMessage->count( $indices );
	}

	public function getActiveGroups(): array
	{
		return $this->getGroups( TRUE );
	}

	public function getGroup( int|string $groupId, bool $activeOnly = FALSE, bool $strict = TRUE ): ?object
	{
		$indices	= ['mailGroupId' => $groupId];
		if( $activeOnly )
			$indices['status']	= [
				Model_Mail_Group::STATUS_ACTIVATED,
				Model_Mail_Group::STATUS_WORKING,
			];
		if( ( $group = $this->modelGroup->getByIndices( $indices ) ) )
			return $group;
		if( !$strict )
			return NULL;
		throw new RangeException( 'Invalid group ID: '.$groupId );
	}

	public function getGroups( bool $activeOnly = FALSE ): array
	{
		$indices	= [];
		if( $activeOnly )
			$indices['status']	= [
				Model_Mail_Group::STATUS_ACTIVATED,
				Model_Mail_Group::STATUS_WORKING,
			];
		$list	= [];
		foreach( $this->modelGroup->getAll( $indices ) as $group )
			$list[$group->mailGroupId]	= $group;
		return $list;
	}

	public function getGroupMember( int|string $memberId, bool $activeOnly = FALSE )
	{
		$indices	= ['mailGroupMemberId' => $memberId];
		if( $activeOnly )
			$indices['status']	= Model_Mail_Group_Member::STATUS_ACTIVATED;
		return $this->modelMember->getByIndices( $indices );
	}

	public function getGroupMembers( int|string $groupId, bool $activeOnly = FALSE ): array
	{
		$indices	= ['mailGroupId' => $groupId];
		if( $activeOnly )
			$indices['status']	= Model_Mail_Group_Member::STATUS_ACTIVATED;
		return $this->modelMember->getAllByIndices( $indices );
	}

	public function getGroupMemberByAddress( int|string $groupId, string $address, bool $activeOnly = FALSE, bool $strict = TRUE ): ?object
	{
		$indices	= ['mailGroupId' => $groupId, 'address' => $address];
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

	public function getMailGroupFromAddress( string $address, bool $activeOnly = FALSE, bool $strict = TRUE ): ?object
	{
		$indices	= ['address' => $address];
		if( $activeOnly )
			$indices['status']	= [
				Model_Mail_Group::STATUS_ACTIVATED,
				Model_Mail_Group::STATUS_WORKING,
			];
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

	public function getMemberByAddress( $address, bool $activeOnly = FALSE, bool $strict = TRUE )
	{
		$indices	= ['address' => $address];
		if( $activeOnly )
			$indices['status']	= Model_Mail_Group_Member::STATUS_ACTIVATED;
		if( ( $member = $this->modelMember->getByIndices( $indices ) ) )
			return $member;
		if( !$strict )
			return NULL;
	}

	/**
	 *	Reads mailbox to find unseen mails and imports them with status STATUS_NEW.
	 *	Imported messages need to be handled afterward by handleImportedGroupMessages.
	 *	@access		public
	 *	@param		int|string		$groupId		Group ID
	 *	@param		boolean			$dry			Flag: Dry mode (default: no)
	 *	@return		object			--list of resulting message ids or import error--
	 */
	public function importGroupMails( int|string$groupId, bool $dry = FALSE ): object
	{
		$results	= (object) [
			'mailsImported'	=> [],
			'errors'		=> [],
		];
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

	public function isGroupMember( int|string $groupId, int|string $memberId ): bool
	{
		return (bool) $this->modelMember->count( [
			'mailGroupMemberId'		=> $memberId,
			'mailGroupId'			=> $groupId,
		] );
	}

	public function isGroupMemberAddress( int|string $groupId, string $address ): bool
	{
		return (bool) $this->modelMember->count( [
			'mailGroupId'		=> $groupId,
			'address'			=> $address,
		] );
	}

	public function registerMemberAction( string $action, int|string $groupId, int|string $memberId, string $message ): object
	{
		$actionId	= $this->modelAction->add( [
			'mailGroupId'		=> $groupId,
			'mailGroupMemberId'	=> $memberId,
			'status'			=> Model_Mail_Group_Action::STATUS_REGISTERED,
			'uuid'				=> ID::uuid(),
			'action'			=> $action,
			'message'			=> $message,
			'createdAt'			=> time(),
			'modifiedAt'		=> time(),
		] );
		return $this->modelAction->get( $actionId );
	}

	public function setGroupBounce( int|string $groupId, $bounce ): void
	{
		$data		= ['bounce' => $bounce];
		$this->updateGroup( $groupId, $data, __METHOD__ );
	}

	public function setGroupStatus( int|string $groupId, $status ): void
	{
		$data		= ['status' => $status];
		$this->updateGroup( $groupId, $data, __METHOD__ );
	}

	public function setGroupTitle( int|string $groupId, $title ): void
	{
		$data		= ['title' => $title];
		$this->updateGroup( $groupId, $data, __METHOD__ );
	}

	public function setGroupType( int|string $groupId, $type ): void
	{
		$data		= ['type' => $type];
		$this->updateGroup( $groupId, $data, __METHOD__ );
	}

	public function setGroupVisibility( int|string $groupId, $visibility ): void
	{
		$data		= ['visibility' => $visibility];
		$this->updateGroup( $groupId, $data, __METHOD__ );
	}

	public function setMemberStatus( int|string $groupId, $memberId, $status ): bool
	{
		$member	= $this->checkMemberId( $memberId );
		if( (int) $member->status === (int) $status )
			return FALSE;
		$group		= $this->checkGroupId( $groupId );
		$mailData	= [
			'group'		=> $group,
			'member'	=> $member,
		];
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

	public function testGestMail( int|string $groupId, $limit = 1 ): void
	{
		return;
	}

	//  --  PROTECTED METHODS --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->modelGroup	= new Model_Mail_Group( $this->env );
		$this->modelMember	= new Model_Mail_Group_Member( $this->env );
		$this->modelMessage	= new Model_Mail_Group_Message( $this->env );
		$this->modelRole	= new Model_Mail_Group_Role( $this->env );
		$this->modelServer	= new Model_Mail_Group_Server( $this->env );
		$this->modelAction	= new Model_Mail_Group_Action( $this->env );
		$this->modelUser	= new Model_User( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicMail	= Logic_Mail::getInstance( $this->env );
	}

	/**
	 *	Tries to create a mailbox using Plesk command line utilities
	 *	@see		https://docs.plesk.com/en-US/onyx/cli-linux/using-command-line-utilities/mail-mail-accounts.39181/
	 *	@todo		finish impl (find a way to execute command as root), run checks beforehand
	 */
	protected function createGroupMailAccountUsingPlesk( int|string $groupId ): void
	{
		$group			= $this->checkGroupId( $groupId );
		if( $group->status !== Model_Mail_Group::STATUS_NEW )
			throw new RuntimeException( 'Mail group be in status STATUS_NEW' );
		$options		= [
			'--create '.$group->address,
			'--passwd '.$group->password,
			'-mailbox true',
			'-mbox_quota 50M',
			'-antivirus inout',
			'-description "'.$group->title.'"',
		];
		$command		= 'plesk bin mail '.join( ' ', $options );

	//	@todo: find a way to execute command as root
		error_log( $command.PHP_EOL, 3, 'commands.log' );
		$this->modelGroup->edit( $groupId, ['status' => Model_Mail_Group::STATUS_EXISTING] );
	}

	protected function getMailbox( int|string $groupId ): PhpImapMailbox
	{
		$group		= $this->checkGroupId( $groupId );
		$server		= $this->modelServer->get( $group->mailGroupServerId );

		$flags		= ['imap'];
		if( (int) $server->imapPort === 993 )
			$flags[]	= 'ssl';
		else if( (int) $server->imapPort === 143 )
			$flags[]	= 'tls';
		$flags		= join( '/', $flags );
		$mailbox	= new PhpImapMailbox(
			sprintf( '{%s:%d/%s}INBOX', $server->imapHost, $server->imapPort, $flags ),
			$group->address,
			$group->password,
			'data/attachments'
		);
		$mailbox->setExpungeOnDisconnect( TRUE );
		return $mailbox;
	}

	protected function setMemberStatusToActivated( $group, $member ): void
	{
		$mailData	= [
			'group'		=> $group,
			'member'	=> $member,
		];
		if( $group->type == Model_Mail_Group::TYPE_REGISTER ){
			if( $member->status == Model_Mail_Group_Member::STATUS_CONFIRMED ){
				$action	= $this->modelAction->getByIndices( [
					'mailGroupId'		=> $group->mailGroupId,
					'mailGroupMemberId'	=> $member->mailGroupMemberId,
					'action'			=> 'confirmAfterJoin',
					'status'			=> Model_Mail_Group_Action::STATUS_HANDLED,
				] );
				if( $action ){
					$this->modelAction->add( [
						'mailGroupId'		=> $group->mailGroupId,
						'mailGroupMemberId'	=> $member->mailGroupMemberId,
						'uuid'				=> ID::uuid(),
						'action'			=> 'informAfterFirstActivate',
						'message'			=> $action->message,
						'createdAt'			=> time(),
						'modifiedAt'		=> time(),
					] );
				}
			}
		}
		$this->modelMember->edit( $member->mailGroupMemberId, [
			'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
			'modifiedAt'	=> time(),
		] );
		$payload	= [
			'group'			=> $group,
			'member'		=> $this->modelMember->get( $member->mailGroupMemberId ),
			'informMembers'	=> TRUE,
		];
		$this->env->getCaptain()->callHook( 'MailGroup', 'memberActivated', $this, $payload );
	}

	protected function setMemberStatusToDeactivated( $group, $member ): void
	{
		$mailData	= [
			'group'		=> $group,
			'member'	=> $member,
		];
		$memberWasActive	= TRUE;
		if( $group->type == Model_Mail_Group::TYPE_REGISTER ){
			if( $member->status == Model_Mail_Group_Member::STATUS_CONFIRMED ){
				$memberWasActive	= FALSE;
			}
		}
		$this->modelMember->edit( $member->mailGroupMemberId, [
			'status'		=> Model_Mail_Group_Member::STATUS_DEACTIVATED,
			'modifiedAt'	=> time(),
		] );
		$payload	= [
			'group'			=> $group,
			'member'		=> $this->modelMember->get( $member->mailGroupMemberId ),
			'informMembers'	=> $memberWasActive,
		];
		$this->env->getCaptain()->callHook( 'MailGroup', 'memberDeactivated', $this, $payload );
	}

	protected function setMemberStatusToRejected( $group, $member ): void
	{
		$mailData	= [
			'group'		=> $group,
			'member'	=> $member,
		];
		$this->modelMember->edit( $member->mailGroupMemberId, [
			'status'		=> Model_Mail_Group_Member::STATUS_REJECTED,
			'modifiedAt'	=> time(),
		] );
		$payload	= [
			'group'			=> $group,
			'member'		=> $this->modelMember->get( $member->mailGroupMemberId ),
		];
		$this->env->getCaptain()->callHook( 'MailGroup', 'memberRejected', $this, $payload );
	}

	protected function updateGroup( int|string $groupId, array $data, $method = NULL )
	{
		$group		= $this->checkGroupId( $groupId );
		$this->modelGroup->edit( $groupId, $data );
		$payload	= [
			'groupId'		=> $groupId,
			'before'		=> $group,
			'changes'		=> $data,
			'method'		=> $method,
		];
		return $this->env->getCaptain()->callHook( 'MailGroup', 'change', $this, $payload );
	}
}
