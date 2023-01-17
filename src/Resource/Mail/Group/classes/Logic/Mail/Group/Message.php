<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_Mail_Group_Message extends Logic
{
	protected $logicGroup;
	protected $modelMember;
	protected $modelMessage;

/*
	protected $modelGroup;
	protected $modelRole;
	protected $modelServer;
	protected $modelAction;
	protected $modelUser;
	protected $logicMail;*/
	protected $transports		= [];

	public function addFromRawMail( $groupId, $rawMail )
	{
		$parser		= new \CeusMedia\Mail\Message\Parser();
		$message	= $parser->parse( $rawMail );
		$headers	= $message->getHeaders();
		$member		= $this->modelMember->getByIndices( array(
			'mailGroupId'	=> $groupId,
			'address'		=> $message->getSender()->getAddress(),
		) );
		$parentId	= 0;
		if( $headers->hasField( 'References' ) ){
			$referenceId	= $headers->getField( 'References' )->getValue();
			$indices		= ['messageId' => '%'.$referenceId];
			if( ( $parent = $this->modelMessage->getByIndices( $indices ) ) )
				$parentId	= $parent->mailGroupMessageId;
		}
		$timestamp	= strtotime( $headers->getField( 'Date' )->getValue() );
		$data		= array(
			'mailGroupId'		=> $groupId,
			'mailGroupMemberId'	=> $member ? $member->mailGroupMemberId : 0,
			'status'			=> Model_Mail_Group_Message::STATUS_NEW,
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

	public function checkId( $messageId, $strict = TRUE )
	{
		$message	= $this->modelMessage->get( $messageId );
		if( $message )
			return $message;
		if( $strict )
			throw new RangeException( 'Invalid message ID: '.$messageId );
		return NULL;
	}

	//  --  PUBLIC METHODS  --  //

	/**
	 *	...s
	 *	@access		public
	 *	@param		integer|object	$messageOrMessageId		Message object or Message ID
	 *	@return		object			Mail message object
	 */
	public function getMessageObject( $messageOrMessageId )
	{
		if( is_int( $messageOrMessageId ) )
			$message	= $this->modelMessage->get( $messageOrMessageId );
		else if( is_object( $messageOrMessageId ) )
			$message	= $messageOrMessageId;
		else
			throw new InvalidArgumentException( 'No valid message object or ID given' );

		$object	= explode( ":", $message->object, 2 );
		if( $object[0] === "BZIP2" )
			return unserialize( bzdecompress( $object[1] ) );
		else if( $object[0] === "GZIP" )
			return unserialize( gzinflate( $object[1] ) );
		return unserialize( $object[1] );
	}

	public function getMessageRawMail( $messageOrMessageId )
	{
//		trigger_error( "Oh! I thought this method is not used anymore.", E_USER_ERROR );
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

/*	public function handleNewMails( $groupId = 0 ){
		trigger_error( "NOT YET IMPLEMENTED.", E_USER_NOTICE );
		$groupIds	= array_keys( $this->getGroups() );
		$groupIds	= $groupId > 0 ? [$groupId] : $groupIds;

		$results	= (object) [
			'mailsImported'	=> [],
			'errors'		=> [],
		];
		foreach( $groupIds as $groupId ){
			$indices	= [
				'groupId'	=> $groupId,
				'status'	=> Model_Mail_Group_Message::STATUS_NEW,
			];
			$orders		= ['createdAt' => 'ASC'];
			$messages	= $this->modelMessage->getAll( $indices, $orders );
			foreach( $messages as $message ){

			}
		}
	}*/

	public function handleImportedGroupMessages( $groupId, $dry = FALSE )
	{
		$senderMemberStatusesToReject	= [
			Model_Mail_Group_Member::STATUS_DEACTIVATED,
			Model_Mail_Group_Member::STATUS_UNREGISTERED,
		];
		$senderMemberStatusesToStall	= [
			Model_Mail_Group_Member::STATUS_REGISTERED,
			Model_Mail_Group_Member::STATUS_CONFIRMED,
		];
		$group		= $this->logicGroup->checkGroupId( $groupId );
//print( 'Group:'.PHP_EOL );
//print_m( $group );
		if( !$group )
			throw new InvalidArgumentException( 'Invalid group ID' );
		$results	= (object) [
			'rejected'		=> [],
			'stalled'		=> [],
			'forwarded'		=> [],
		];
		$indices	= [
			'mailGroupId'		=> $groupId,
			'status'			=> Model_Mail_Group_Message::STATUS_NEW,
		];
		$orders		= ['createdAt' => 'ASC'];
		$messages	= $this->modelMessage->getAllByIndices( $indices, $orders );
		foreach( $messages as $message ){

			if( (int) $message->mailGroupMemberId ){
				$sender	= $this->logicGroup->getGroupMember( $message->mailGroupMemberId );
				if( !$this->logicGroup->isGroupMember( $groupId, $sender->mailGroupMemberId ) ){
					if( (int) $group->type === Model_Mail_Group::TYPE_AUTOJOIN ){
						$this->logicGroup->addGroupMember( $message->mailGroupMemberId, $sender->mailGroupMemberId );
					}
					else{

					}
				}

				if( in_array( (int) $sender->status, $senderMemberStatusesToReject ) ){
					$results->rejected[]	= $message;
					if( !$dry )
						$this->rejectMessage( $message );
					continue;
				}
				if( in_array( (int) $sender->status, $senderMemberStatusesToStall ) ){
					$results->stalled[]	= $message;
					if( !$dry )
						$this->stallMessage( $message );
					continue;
				}
				if( !$dry )
					$message->sentMails	= $this->forwardMessage( $message, $dry );
				$results->forwarded[]	= $message;
			}
			else{
				switch( (int) $group->type ){
					case Model_Mail_Group::TYPE_AUTOJOIN:
						if( !$dry ){
							$this->logicGroup->autojoinMemberByMessage( $groupId, $message );
							$message	= $this->checkId( $message->mailGroupMessageId );
//							$this->stallMessage( $message );
//							$results->stalled[]	= $message;
							$message->sentMails	= $this->forwardMessage( $message, $dry );
							$results->forwarded[]	= $message;
						}
						break;
					case Model_Mail_Group::TYPE_JOIN:
					case Model_Mail_Group::TYPE_REGISTER:
					case Model_Mail_Group::TYPE_INVITE:
						$results->rejected[]	= $message;
						if( !$dry )
							$this->rejectMessage( $message );
						break;
				}
			}
		}
		return $results;
	}

	public function handleStalledGroupMessages( $groupId, $dry = FALSE )
	{
		$group		= $this->logicGroup->checkGroupId( $groupId );

		$results	= (object) [
			'forwarded'	=> [],
			'rejected'	=> [],
		];
		$indices	= [
			'mailGroupId'	=> $groupId,
			'status'		=> Model_Mail_Group_Message::STATUS_STALLED,
		];
		$orders		= ['createdAt' => 'ASC'];
		$messages	= $this->modelMessage->getAllByIndices( $indices, $orders );
		foreach( $messages as $message ){
			$member	= $this->logicGroup->getGroupMember( $message->mailGroupMemberId );
			if( $member && $member->status == Model_Mail_Group_Member::STATUS_ACTIVATED ){
				$message->sentMails	= $this->forwardMessage( $message, $dry );
				$results->forwarded[]	= $message;
			}
		}
		return $results;
	}


	//  --  PROTECTED METHODS  --  //

	protected function __onInit(): void
	{
		$this->logicGroup	= Logic_Mail_Group::getInstance( $this->env );
		$this->modelMember	= new Model_Mail_Group_Member( $this->env );
		$this->modelMessage	= new Model_Mail_Group_Message( $this->env );
/*		$this->modelGroup	= new Model_Mail_Group( $this->env );
		$this->modelServer	= new Model_Mail_Group_Server( $this->env );
		$this->modelUser	= new Model_User( $this->env );
		$this->logicMail	= Logic_Mail::getInstance( $this->env );*/
	}

	protected function forwardMessage( $message, bool $dryMode = FALSE )
	{
		$group	= $this->logicGroup->getGroup( $message->mailGroupId, TRUE, TRUE );
		$allowedMessageStatuses	= [
			Model_Mail_Group_Message::STATUS_NEW,
			Model_Mail_Group_Message::STATUS_STALLED,
		];
		if( !in_array( (int) $message->status, $allowedMessageStatuses ) )
			throw new RuntimeException( 'Only new or stalled messages can be sent' );
		if( !$message->mailGroupMemberId )
			throw new RuntimeException( 'Message sender is not assigned to a group member' );
		$mails		= [];
		$members	= $this->logicGroup->getGroupMembers( $group->mailGroupId, TRUE );
		foreach( $members as $member ){
			$mails[]	= $this->forwardMessageToMember( $message, $member, $dryMode );
		}
		return $mails;
	}

	protected function forwardMessageToMember( $messageObjectOrId, $memberObjectOrId, bool $dry = FALSE )
	{
		if( is_object( $messageObjectOrId ) )
			$message	= $messageObjectOrId;
		else if( is_int( $messageObjectOrId ) )
			$message	= $this->checkId( $messageObjectOrId );
		else
			throw new InvalidArgumentException( 'No valid message object or ID given' );

		if( is_object( $memberObjectOrId ) )
			$member	= $memberObjectOrId;
		else if( is_int( $memberObjectOrId ) )
			$member	= $this->checkMemberId( $memberObjectOrId );
		else
			throw new InvalidArgumentException( 'No valid member object or ID given' );

		$group		= $this->logicGroup->checkGroupId( $message->mailGroupId );
		if( !$group )
			throw new InvalidArgumentException( 'Invalid group ID' );

		if( !$message->mailGroupMemberId )
			throw new RuntimeException( 'Message sender is not assigned' );
		$senderMember		= $this->logicGroup->getGroupMember( $message->mailGroupMemberId );
		if( !$senderMember )
			throw new RuntimeException( 'Message sender is not valid' );
		$senderAddress	= new \CeusMedia\Mail\Address( $senderMember->address, $senderMember->title );

		$mailObject		= $this->getMessageObject( (int) $message->mailGroupMessageId );
		$forwardMail	= new \CeusMedia\Mail\Message();
		foreach( $mailObject->getParts( TRUE ) as $part ){
			$part->setEncoding( 'base64' );
			if( strlen( trim( $part->getContent() ) ) )
				$forwardMail->addPart( $part );
		}
		if( !count( $forwardMail->getParts( TRUE ) ) ){
			// @todo	handle this situation - do not return only
			remark( 'No mails parts set' );
			return;
		}

		$forwardMail->setSender( $senderAddress );
		$forwardMail->addReplyTo( new \CeusMedia\Mail\Address( $group->address ) );
		$forwardMail->setSubject( $mailObject->getSubject() );
		$forwardMail->addHeaderPair( 'Precedence', 'list' );
		$forwardMail->addHeaderPair( 'List-Post', '<mailto:'.$group->address.'>' );
		$forwardMail->addHeaderPair( 'Reply-To', $group->address );
		if( !empty( $group->bounce ) )
			$message->addHeaderPair( 'Errors-To', $group->bounce );
		$recipient	= new \CeusMedia\Mail\Address( $member->address, $member->title );
		$forwardMail->addRecipient( $recipient );
//		remark( '    Send to: '.$recipient->get() );
		if( !$dry ){
			$this->getTransport( $message->mailGroupId )->send( $forwardMail );
			$this->setMessageStatus(
				$message->mailGroupMessageId,
				Model_Mail_Group_Message::STATUS_FORWARDED,
				__METHOD__
			);
		}
		return $forwardMail;
	}

	protected function getMailGroupLogic()
	{
		return $this->env->getLogic()->get( 'mailGroupMessage' );
	}

	protected function getTransport( $groupId )
	{
		$groupId	= (int) $groupId;
		if( !array_key_exists( $groupId, $this->transports ) ){
			$group	= $this->logicGroup->checkGroupId( $groupId );
			$server	= $this->logicGroup->checkServerId( $group->mailGroupServerId );
			$this->transports[$groupId]  = new \CeusMedia\Mail\Transport\SMTP(
				$server->smtpHost,
				(int) $server->smtpPort,
				$group->address,
				$group->password
			);
		}
		return $this->transports[$groupId];
	}

	protected function rejectMessage( $messageObjectOrId )
	{
		if( is_object( $messageObjectOrId ) )
			$message	= $messageObjectOrId;
		else if( is_int( $messageObjectOrId ) )
			$message	= $this->checkId( $messageObjectOrId );
		else
			throw new InvalidArgumentException( 'No valid message object or ID given' );

		$this->setMessageStatus(
			$message->mailGroupMessageId,
			Model_Mail_Group_Message::STATUS_REJECTED,
			__METHOD__
		);
		//	... @todo send mail to sender
	}

	protected function stallMessage( $messageObjectOrId )
	{
		if( is_object( $messageObjectOrId ) )
			$message	= $messageObjectOrId;
		else if( is_int( $messageObjectOrId ) )
			$message	= $this->checkId( $messageObjectOrId );
		else
			throw new InvalidArgumentException( 'No valid message object or ID given' );

		$this->setMessageStatus(
			$message->mailGroupMessageId,
			Model_Mail_Group_Message::STATUS_STALLED,
			__METHOD__
		);
		//	... @todo send mail to sender
	}

	//  --  PRIVATE METHODS  --  //

	private function setMessageStatus( $messageId, $status, $method = NULL )
	{
		$message	= $this->checkId( $messageId );
		$data		= array(
			'status'		=> $status,
			'modifiedAt'	=> time(),
		);
		$result		= $this->modelMessage->edit( $messageId, $data );
		$payload	= [
			'before'	=> $message,
			'changes'	=> $data,
			'method'	=> $method,
		];
		$this->env->getCaptain()->callHook( 'MailGroupMessage', 'change', $this, $payload );
		return $result;
	}
}
