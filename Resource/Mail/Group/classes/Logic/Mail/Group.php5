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
		$this->logicMail	= new Logic_Mail( $this->env );
	}

	protected function checkGroupId( $groupId, $strict = TRUE ){
		$group	= $this->modelGroup->get( $groupId );
		if( $group )
			return $group;
		if( $strict )
			throw new RangeException( 'Invalid group ID: '.$groupId );
		return NULL;
	}

	protected function checkServerId( $serverId, $strict = TRUE ){
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

	protected function forwardMailTo( $groupId, $mail, \CeusMedia\Mail\Address $sender, \CeusMedia\Mail\Address $receiver, $dry = FALSE ){
		$group		= $this->checkGroupId( $groupId );
		$message	= new \CeusMedia\Mail\Message();
		if( strlen( trim( $mail->textPlain ) ) )
			$message->addText( $mail->textPlain );
		if( strlen( trim( $mail->textHtml ) ) )
			$message->addHtml( $mail->textHtml );
		if( !count( $message->getParts() ) )
			return;
		$message->setSender( $sender );
		$message->addReplyTo( new \CeusMedia\Mail\Address( $group->address ) );
		$message->setSubject( $mail->subject );
		$message->addHeaderPair( 'Precedence', 'list' );
		$message->addHeaderPair( 'List-Post', '<mailto:'.$group->address.'>' );
		$message->addHeaderPair( 'Reply-To', $group->address );
		if( !empty( $group->bounce ) )
			$message->addHeaderPair( 'Errors-To', $group->bounce );
		$message->addRecipient( $receiver );
//		remark( '    Send to: '.$receiver->get() );
		if( !$dry )
			$this->getTransport( $groupId )->send( $message );
	}

	public function getActiveGroups(){
		return $this->getGroups( TRUE );
	}

	public function getGroup( $groupId, $activeOnly = FALSE, $strict = TRUE ){
		$indices	= array( 'mailGroupId' => $groupId );
		if( $activeOnly )
			$indices['status']	= Model_Mail_Group::STATUS_ACTIVATED;
		if( ( $group = $this->modelGroup->getByIndices( $indices ) ) )
			return $group;
		if( !$strict )
			return NULL;
		throw new RangeException( 'Invalid group ID: '.$groupId );
	}

	public function getGroups( $activeOnly = FALSE ){
		$indices	= array();
		if( $activeOnly )
			$indices['status']	= Model_Mail_Group::STATUS_ACTIVATED;
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
			$indices['status']	= Model_Mail_Group::STATUS_ACTIVATED;
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

	public function getMessageObject( $messageOrMessageId ){
		if( is_object( $messageOrMessageId ) )
			$message	= $messageOrMessageId;
		else if( is_int( $messageOrMessageId ) )
			$message	= $this->modelMessage->get( $messageOrMessageId );
		if( !$message || !isset( $message->object ) )
			throw new InvalidArgumentException( 'Given message is invalid' );

		$object	= explode( ":", $message->object, 2 );
		if( $object[0] === "BZIP2" )
			return unserialize( bzdecompress( $object[1] ) );
		else if( $object[0] === "GZIP" )
			return unserialize( gzinflate( $object[1] ) );
		return unserialize( $object[1] );
	}

	public function getRawMailFromMessage( $messageOrMessageId ){
		if( is_object( $messageOrMessageId ) )
			$message	= $messageOrMessageId;
		else if( is_int( $messageOrMessageId ) )
			$message	= $this->modelMessage->get( $messageOrMessageId );
		if( !$message || !isset( $message->object ) )
			throw new InvalidArgumentException( 'Given message is invalid' );

		$raw	= explode( ":", $message->raw, 2 );
		if( $raw[0] === "BZIP2" )
			return bzdecompress( $raw[1] );
		if( $raw[0] === "GZIP" )
			return gzinflate( $raw[1] );
		return $raw[1];
	}

	protected function getTransport( $groupId ){
		$group	= $this->checkGroupId( $groupId );
		$server	= $this->checkServerId( $group->mailGroupServerId );

		if( !isset( $this->transports[(int) $groupId] ) )
			$this->transports[(int) $groupId]  = new \CeusMedia\Mail\Transport\SMTP(
				$server->smtpHost,
				(int) $server->smtpPort,
				$group->address,
				$group->password
			);
		return $this->transports[(int) $groupId];
	}

	public function handleNewMails( $groupId = 0 ){
		$groupIds	= array_keys( $this->getGroups() );
		$groupIds	= $groupId > 0 ? array( $groupId ) : $groupIds;

		$results	= (object) array(
			'mailsImported'	=> array(),
			'errors'		=> array(),
		);
		foreach( $groupIds as $groupId ){
			$indices	= array(
				'groupId'	=> $groupId,
				'status'	=> Model_Mail_Group_Message::STATUS_NEW,
			);
			$orders		= array( 'createdAt' => 'ASC' );
			$messages	= $this->modelMessage->getAll( $indices, $orders );
			foreach( $messages as $message ){

			}
		}
	}

	public function handleMailgroup( $groupId, $dry = FALSE, $verbose = NULL ){
		$group		= $this->checkGroupId( $groupId );
		$mailbox	= $this->getMailbox( $groupId );
		$mails		= $this->getUnhandledNewMails( $mailbox );
		if( !$mails )
			return (object) array( 'mails' => array() );
//		remark( 'Handling mailgroup: '.$group->title );
		$list		= array();
		foreach( $mails as $mailId => $mail ){
//			remark( '- Mail #'.$mailId );
//			remark( '  Sender: '.$mail->fromAddress );
//			remark( '  Subject: '.$mail->subject );
			if( !$this->isGroupMember( $groupId, $mail->fromAddress ) ){
//				remark( '    Skipped since <'.$mail->fromAddress.'>" is not a member' );
			//	@todo: send negative reply mail, inform mail admin, find decision based on future mail group settings
				continue;
			}
			$member	= $this->getGroupMemberByAddress( $groupId, $mail->fromAddress );
			$sender	= new \CeusMedia\Mail\Address( $member->address );
			$sender->setName( $member->title );

			$mail->receivers	= array();
			$members	= $this->modelMember->getAllByIndices( array(
				'mailGroupId'	=> $groupId,
				'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
			) );
			foreach( $members as $member ){
				if( $mail->fromAddress === $member->address )
					continue;
				$recipient	= new \CeusMedia\Mail\Address( $member->address );
				if( $member->title )
					$recipient->setName( $member->title );
				$mail->receivers[]	= $recipient;
			}
			if( !$mail->receivers ){
//				remark( ' - Skipped since no members than other than sender' );
			//	@todo: send negative reply mail, inform mail admin, find decision based on future mail group settings
				continue;
			}

//			remark( '  Forwarding to '.count( $mail->receivers ).' receivers:' );
			foreach( $mail->receivers as $receiver )
				$this->forwardMailTo( $groupId, $mail, $sender, $receiver, $dry );
			if( !$dry )
				$mailbox->markMailAsRead( $mailId );
			$list[]	= $mail;
		}
		return (object) array( 'mails' => $list );
	}

	protected function importNewMail( $groupId, $rawMail ){
		$message	= \CeusMedia\Mail\Message\Parser::parse( $rawMail );
		$headers	= $message->getHeaders();
		$member		= $this->modelMember->getByIndices( array(
			'mailGroupId'	=> $groupId,
			'address'		=> $message->getSender()->getAddress(),
		) );
		$parentId	= 0;
		if( $headers->hasField( 'References' ) ){
			$referenceId	= $headers->getField( 'References' )->getValue();
			$indices		= array( 'messageId' => '%'.$referenceId );
			if( ( $parent = $this->modelMessage->getByIndices( $indices ) ) )
				$parentId	= $parent->mailGroupMessageId;
		}
		$timestamp	= strtotime( $headers->getField( 'Date' )->getValue() );
		$data	= array(
			'mailGroupId'		=> $groupId,
			'mailGroupMemberId'	=> $member ? $member->mailGroupMemberId : 0,
			'status'			=> Model_Mail_Group_Message::STATUS_FORWARDED,
			'parentId'			=> $parentId,
			'messageId'			=> $headers->getField( 'Message-ID' )->getValue(),
			'createdAt'			=> $timestamp,
		);
		$compression	= "bzip2";
		if( $compression === "bzip2" ){
			$data['raw']		= 'BZIP2:'.bzcompress( $rawMail );
			$data['object']		= 'BZIP2:'.bzcompress( serialize( $message ) );
		}
		else if( $compression === "gzip" ){
			$data['raw']		= 'GZIP:'.gzdeflate( $rawMail );
			$data['object']		= 'GZIP:'.gzdeflate( serialize( $message ) );
		}
		return $this->modelMessage->add( $data, FALSE );
	}

	public function importNewMails( $groupId = 0, $dry = FALSE ){
		$groupIds	= array_keys( $this->getGroups() );
		$groupIds	= $groupId > 0 ? array( $groupId ) : $groupIds;
		$results	= (object) array(
			'mailsImported'	=> array(),
			'errors'		=> array(),
		);
		foreach( $groupIds as $groupId ){
			$mailbox	= $this->getMailbox( $groupId );
			$mailIds	= $mailbox->searchMailbox( 'UNSEEN' );
//			$mailIds	= $limit > 0 ? array_slice( $mailIds, 0, $limit ) : $mailIds;
			foreach( $mailIds as $mailId ){
				$mail		= $mailbox->getRawMail( $mailId );
				try{
					$messageId	= $this->importNewMail( $groupId, $mail );
					$results->mailsImported[]	= $messageId;
					if( !$dry )
						$mailbox->markMailAsRead( $mailId );
				}
				catch( Exception $e ){
					$results->errors[]	= $e->getMessage();
				}
			}
		}
		return $results;
	}

	public function isGroupMember( $groupId, $address ){
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


	public function testGestMail( $groupId, $limit = 1 ){
		return;
	}


/*
	protected function getUnhandledNewMails( $mailbox, $limit = NULL ){
		$mails		= array();
		$mailIds	= $mailbox->searchMailbox( 'UNSEEN' );
		$mailIds	= $limit > 0 ? array_slice( $mailIds, 0, $limit ) : $mailIds;
		foreach( $mailIds as $mailId )
			$mails[$mailId]	= $mailbox->getMail( $mailId, FALSE );
		return $mails;
	}
*/
}

?>
