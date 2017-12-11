<?php
class Logic_Mail_Group extends CMF_Hydrogen_Logic{

	protected $modelGroup;
	protected $modelMember;
	protected $modelRole;
	protected $modelServer;
	protected $transports		= array();

	public function __onInit(){
		$this->modelGroup	= new Model_Mail_Group( $this->env );
		$this->modelMember	= new Model_Mail_Group_Member( $this->env );
		$this->modelRole	= new Model_Mail_Group_Role( $this->env );
		$this->modelServer	= new Model_Mail_Group_Server( $this->env );
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

	protected function getTransport( $groupId ){
		$group	= $this->checkGroupId( $groupId );
		$server	= $this->checkServerId( $group->mailGroupServerId );

		if( !isset( $this->transports[(int) $groupId] ) )
			$this->transports[(int) $groupId]  = new \CeusMedia\Mail\Transport\SMTP(
				$server->host,
				(int) $server->port,
				$group->address,
				$group->password
			);
		return $this->transports[(int) $groupId];
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
		return $this->modelGroup->getAll( array(
			'status'	=> Model_Mail_Group::STATUS_ACTIVATED,
		) );
	}

	public function getGroupMemberByAddress( $groupId, $address ){
		$members	= $this->modelMember->getAllByIndex( 'mailGroupId', $groupId );
		foreach( $members as $member )
			if( $member->address === $address )
				return $member;
		$msg	= 'Member address <%s> is not in group "%s"';
		throw new RangeException( sprintf( $msg, $address, $mailgroup->title ) );
	}

	public function getGroup( $groupId ){
		$group		= $this->modelGroup->get( $groupId );
		if( $group )
			return $group;
		throw new RangeException( 'Invalid group ID: '.$groupId );
	}

	protected function getMailbox( $groupId ){
		$group		= $this->checkGroupId( $groupId );
		$server		= $this->modelServer->get( $group->mailGroupServerId );

		$flags		= array( 'imap' );
		if( (int) $server->port === 993 )
			$flags[]	= 'ssl';
		$flags		= join( '/', $flags );
		$mailbox	= new \PhpImap\Mailbox(
			sprintf( '{%s:%d/%s}INBOX', $server->host, $server->port, $flags ),
			$group->address,
			$group->password,
			'data/attachments'
		);
		$mailbox->setExpungeOnDisconnect( TRUE );
		return $mailbox;
	}

	protected function getUnhandledNewMails( $mailbox, $limit = NULL ){
		$mails		= array();
		$mailIds	= $mailbox->searchMailbox( 'UNSEEN' );
		$mailIds	= $limit > 0 ? array_slice( $mailIds, 0, $limit ) : $mailIds;
		foreach( $mailIds as $mailId )
			$mails[$mailId]	= $mailbox->getMail( $mailId, FALSE );
		return $mails;
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

	public function isGroupMember( $groupId, $address ){
		return (bool) $this->modelMember->count( array(
			'mailGroupId'		=> $groupId,
			'address'			=> $address,
		) );
	}
}

?>
