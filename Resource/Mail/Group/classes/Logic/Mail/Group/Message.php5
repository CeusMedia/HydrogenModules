<?php
class Logic_Mail_Group_Message extends CMF_Hydrogen_Logic{

	protected $logicGroup;
	protected $modelMember;
	protected $modelMessage;
/*
	protected $modelGroup;
	protected $modelRole;
	protected $modelServer;
	protected $modelAction;
	protected $modelUser;
	protected $logicMail;
	protected $transports		= array();*/

	public function __onInit(){
		$this->logicGroup	= new Logic_Mail_Group( $this->env );
		$this->modelMember	= new Model_Mail_Group_Member( $this->env );
		$this->modelMessage	= new Model_Mail_Group_Message( $this->env );
/*		$this->modelGroup	= new Model_Mail_Group( $this->env );
		$this->modelServer	= new Model_Mail_Group_Server( $this->env );
		$this->modelUser	= new Model_User( $this->env );
		$this->logicMail	= Logic_Mail::getInstance( $this->env );*/
	}

	public function checkId( $messageId, $strict = TRUE ){
		$message	= $this->modelMessage->get( $messageId );
		if( $message )
			return $message;
		if( $strict )
			throw new RangeException( 'Invalid message ID: '.$messageId );
		return NULL;
	}

	protected function getMailGroupLogic(){
		return $this->env->getLogic()->get( 'mailGroupMessage' );
	}

	//  --  PUBLIC METHODS  --  //

	/**
	 *	...s
	 *	@access		public
	 *	@param		integer|object	$messageOrMessageId		Message object or Message ID
	 *	@return		object			Mail message object
	 */
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
	}*/

	public function handleNewMessages( $groupId ){
		$senderMemberStatusesToReject	= array(
			Model_Mail_Group_Member::STATUS_DEACTIVATED,
			Model_Mail_Group_Member::STATUS_UNREGISTERED,
		);
		$senderMemberStatusesToStall	= array(
			Model_Mail_Group_Member::STATUS_NEW,
			Model_Mail_Group_Member::STATUS_CONFIRMED,
		);
		$group		= $this->getMailGroupLogic( 'mailGroup' )->checkGroupId( $groupId );
		$indices	= array(
			'mailGroupId'	=> $groupId,
			'status'		=> Model_Mail_Group_Message::STATUS_NEW,
		);
		$orders		= array( 'createdAt' => 'ASC' );
		$messages	= $this->modelMessage->getAllByIndices( $indices, $orders );
		foreach( $messages as $message ){
			if( $message->mailGroupMemberId ){
				if( in_array( (int) $member->status, $senderMemberStatusesToReject ) ){
					$this->rejectMessage( $group, $message );
					continue;
				}
				if( in_array( (int) $member->status, $senderMemberStatusesToStall ) ){
					$this->stallMessage( $group, $message );
					continue;
				}
			}
			else{
				switch( (int) $group->type ){
					case Model_Mail_Group::TYPE_PUBLIC:
						throw new Exception( 'Not implemented yet (see Logic_Mail_Group@270)' );
						//  @todo extract sender address from mail
						$member	= array(
							'username'	=> '',
							'email'		=> '',
						);
						$mailData	= array(
							'member'	=> $member,
							'group'		=> $group,
							'greeting'	=> $action->message,
						);
						$mail		= new Mail_Info_Mail_Group_Autojoined( $this->env );
						$receiver	= (object) $member;
						$language	= $this->env->getLanguage()->getLanguage();
						$this->logicMail->handleMail( $mail, $receiver, $language );
						break;
					case Model_Mail_Group::TYPE_JOIN:
					case Model_Mail_Group::TYPE_REGISTER:
					case Model_Mail_Group::TYPE_INVITE:
						$this->rejectMessage( $group, $message );
						break;
				}
			}
		}
	}

	public function handleStalledMessages( $groupId ){
		$group		= $this->getMailGroupLogic( 'mailGroup' )->checkGroupId( $groupId );
//		$group		= $this->env->logic->mailGroup->checkGroupId( $groupId );
		$indices	= array(
			'mailGroupId'	=> $groupId,
			'status'		=> Model_Mail_Group_Message::STATUS_STALLED,
		);
		$orders		= array( 'createdAt' => 'ASC' );
		$messages	= $this->modelMessage->getAllByIndices( $indices, $orders );
		foreach( $messages as $message ){
			if( $message->status ){

			}
		}
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



	//  --  PROTECTED METHODS  --  //

	protected function forwardMailTo( $groupId, $mail, \CeusMedia\Mail\Address $sender, \CeusMedia\Mail\Address $receiver, $dry = FALSE ){
		trigger_error( "Deprecated function called.", E_USER_NOTICE );
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

	protected function rejectMessage( $group, $message ){
		//	... @todo send mail to sender
		return $this->modelMessage->edit( $message->mailGroupMessageId, array(
			'status'		=> Model_Mail_Group_Message::STATUS_REJECTED,
			'modifiedAt'	=> time(),
		) );
	}

	protected function stallMessage( $group, $message ){
		return $this->modelMessage->edit( $message->mailGroupMessageId, array(
			'status'		=> Model_Mail_Group_Message::STATUS_STALLED,
			'modifiedAt'	=> time(),
		) );
	}
}
