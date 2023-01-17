<?php
class Job_Mail_Group extends Job_Abstract
{
	protected $logicGroup;
	protected $logicMail;
	protected $logicMessage;

	public function activateConfirmedMembers()
	{
		$modelMember	= new Model_Mail_Group_Member( $this->env );
		$modelAction	= new Model_Mail_Group_Action( $this->env );
		$modelUser		= new Model_User( $this->env );
		$groups			= $this->logicGroup->getGroups();
		$count			= 0;
		foreach( $groups as $group ){
			if( $group->type == Model_Mail_Group::TYPE_AUTOJOIN )
				continue;
			if( $group->type == Model_Mail_Group::TYPE_INVITE )
				continue;

			$indices	= [
				'mailGroupId'	=> $group->mailGroupId,
				'status'		=> Model_Mail_Group_Member::STATUS_CONFIRMED,
			];
			$members	= $modelMember->getAllByIndices( $indices );
			if( $members )
				$this->out( 'Mail Group: '.$group->title );
			foreach( $members as $member ){
				if( !$this->dryMode )
					$modelMember->edit( $member->mailGroupMemberId, array(
						'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
						'modifiedAt'	=> time(),
					) );

				$action	= $modelAction->getByIndices( [
					'action'			=> 'activateAfterConfirm',
					'mailGroupId'		=> $group->mailGroupId,
					'mailGroupMemberId'	=> $member->mailGroupMemberId,
					'status'			=> 0,
				] );

				if( $action ){
					$count++;
					$manager	= $modelUser->get( $group->managerId );
					foreach( $this->logicGroup->getGroupMembers( $group->mailGroupId, TRUE ) as $entry ){
						if( $entry->address === $manager->email )
							continue;
						if( $entry->mailGroupMemberId === $member->mailGroupMemberId )
							continue;
						$mailData	= [
							'member'	=> $member,
							'group'		=> $group,
							'greeting'	=> $action->message,
						];
						$receiver	= (object) [
							'username'	=> $entry->title,
							'email'		=> $entry->address
						];
						$mail		= new Mail_Info_Mail_Group_Members_MemberJoined( $this->env, $mailData );
						$language	= $this->env->getLanguage()->getLanguage();
						if( !$this->dryMode )
							$this->logicMail->handleMail( $mail, $receiver, $language );
					}
					$mail		= new Mail_Info_Mail_Group_Member_Activated( $this->env, $mailData );
					$receiver	= (object) [
						'username'	=> $member->title,
						'email'		=> $member->address
					];
					$language	= $this->env->getLanguage()->getLanguage();
					$this->logicMail->appendRegisteredAttachments( $mail, $language );
					if( !$this->dryMode ){
						$this->logicMail->handleMail( $mail, $receiver, $language );
						$modelAction->edit( $action->mailGroupActionId, array(
							'status'		=> Model_Mail_Group_Action::STATUS_HANDLED,
							'modifiedAt'	=> time(),
						) );
					}
				}
				$this->out( '- Member "'.$member->title.'" <'.$member->address.'> activated' );
			}
		}
		$this->out( $count.' members activated' );
	}

	public function informMembersAboutNewMember()
	{
		$modelMember	= new Model_Mail_Group_Member( $this->env );
		$modelAction	= new Model_Mail_Group_Action( $this->env );
		$modelUser		= new Model_User( $this->env );
		$groups			= $this->logicGroup->getGroups();
		$count			= 0;
		foreach( $groups as $group ){
			$actions	= $modelAction->getAllByIndices( [
				'action'			=> 'informAfterFirstActivate',
				'mailGroupId'		=> $group->mailGroupId,
				'status'			=> 0,
			] );
			if( $actions ){
				$manager	= $modelUser->get( $group->managerId );
				foreach( $actions as $action ){
					$count++;
					$member		= $modelMember->get( $action->mailGroupMemberId );
					if( !$member ){
						$this->out( 'Error: Invalid member ID ('.$action->mailGroupMemberId.') in action ('.$action->mailGroupActionId.')' );
						continue;
					}
					$groupMembers	= $this->logicGroup->getGroupMembers( $group->mailGroupId, TRUE );
					$this->out( '- Informing '.count( $groupMembers ).' group members about new member "'.$member->title.'" <'.$member->address.'>:' );
					foreach( $groupMembers as $entry ){
						if( $entry->address === $manager->email )
							continue;
						if( $entry->mailGroupMemberId === $member->mailGroupMemberId )
							continue;
						$mailData	= [
							'member'	=> $member,
							'group'		=> $group,
							'greeting'	=> $action->message,
						];
						$receiver	= (object) [
							'username'	=> $entry->title,
							'email'		=> $entry->address
						];
						$mail		= new Mail_Info_Mail_Group_Members_MemberJoined( $this->env, $mailData );
						$language	= $this->env->getLanguage()->getLanguage();
						if( !$this->dryMode )
							$this->logicMail->handleMail( $mail, $receiver, $language );
						$this->out( '  - Member: "'.$entry->title.'" <'.$entry->address.'>' );
					}
					$mail		= new Mail_Info_Mail_Group_Member_Activated( $this->env, $mailData );
					$receiver	= (object) [
						'username'	=> $member->title,
						'email'		=> $member->address
					];
					$language	= $this->env->getLanguage()->getLanguage();
					$this->logicMail->appendRegisteredAttachments( $mail, $language );
					if( !$this->dryMode ){
						$this->logicMail->handleMail( $mail, $receiver, $language );
						$modelAction->edit( $action->mailGroupActionId, array(
							'status'		=> Model_Mail_Group_Action::STATUS_HANDLED,
							'modifiedAt'	=> time(),
						) );
					}
				}
			}
		}
		$this->out( $count.' members activated' );
	}

	public function test()
	{
		$this->out( 'PHP Version: '.phpversion() );
		$this->out( 'Dry Mode: '.( $this->dryMode ? 'yes' : 'no' ) );
		$this->out( 'Verbose Mode: '.( $this->verbose ? 'yes' : 'no' ) );
		$this->out( 'DEPRECATED: Use job Mail.Group.handle with dry mode, instead!' );
		throw new Exception( 'Test exception thrown' );
	}

	public function handle()
	{
		if( $this->dryMode ){
			$this->out( 'DRY RUN - no changes will be made.' );
//			$this->out( 'Would send '.$count.' mails.' );
		}
		$groups		= $this->logicGroup->getActiveGroups();
		foreach( $groups as $group ){
			if( (int) $group->status === Model_Mail_Group::STATUS_WORKING )
				continue;
			$groupId	= $group->mailGroupId;
//			$this->logicGroup->setGroupStatus( $groupId, Model_Mail_Group::STATUS_WORKING );
			$this->out( '* Group: '.$group->title.' (ID: '.$groupId.')' );
			$this->out( '  - Date: '.date( 'r' ) );

			try{
				//  handle formerly stalled messages
				$results	= $this->logicMessage->handleStalledGroupMessages( $groupId, $this->dryMode );
				if( $results->forwarded ){
					$this->out( '  - '.count( $results->forwarded ).' messages forwarded' );
					foreach( $results->forwarded as $message ){
						$mailObject	= $this->logicMessage->getMessageObject( (int) $message->mailGroupMessageId );
						$this->out( '    Sender: '.$mailObject->getSender()->get() );
						$this->out( '    Subject: '.$mailObject->getSubject() );
					}
				}
				if( $results->rejected ){
					$this->out( '  - '.count( $results->rejected ).' messages rejected' );
					foreach( $results->rejected as $message ){
						$mailObject	= $this->logicMessage->getMessageObject( (int) $message->mailGroupMessageId );
						$this->out( '    Sender: '.$mailObject->getSender()->get() );
						$this->out( '    Subject: '.$mailObject->getSubject() );
					}
				}

				//  import new mails from mailbox
				$results	= $this->logicGroup->importGroupMails( $groupId, $this->dryMode );
				if( $results->errors ){
					$this->out( '  - '.count( $results->errors ).' errors:' );
						foreach( $results->errors as $error )
						$this->out( '    * '.$error );
				}
				if( $results->mailsImported ){
					$this->out( '  - '.count( $results->mailsImported ).' mails imported' );
					foreach( $results->mailsImported as $messageId ){
						if( !$messageId )															//  dry mode has been enabled
							continue;
						$message	= $this->logicMessage->checkId( $messageId );
						$mailObject	= $this->logicMessage->getMessageObject( (int) $message->mailGroupMessageId );
						$this->out( '    Sender: '.$mailObject->getSender()->get() );
						$this->out( '    Subject: '.$mailObject->getSubject() );
	/*					$this->out( '- Mail #'.$handledMail->id );
						$this->out( '  Sender: '.$handledMail->fromAddress );
						$this->out( '  Receivers: '.count( $handledMail->receivers ) );
	*/				}
				}

				//  handle new messages
				$results	= $this->logicMessage->handleImportedGroupMessages( $groupId, $this->dryMode );
				if( $results->forwarded ){
					$this->out( '  - '.count( $results->forwarded ).' messages forwarded' );
					foreach( $results->forwarded as $message ){
						$mailObject	= $this->logicMessage->getMessageObject( (int) $message->mailGroupMessageId );
						$this->out( '    Subject: '.$mailObject->getSubject() );
						$this->out( '    Sender: '.$mailObject->getSender()->get() );
					}
				}
				if( $results->stalled ){
					$this->out( '  - '.count( $results->stalled ).' messages stalled' );
					foreach( $results->stalled as $message ){
						$mailObject	= $this->logicMessage->getMessageObject( (int) $message->mailGroupMessageId );
						$this->out( '    Sender: '.$mailObject->getSender()->get() );
						$this->out( '    Subject: '.$mailObject->getSubject() );
					}
				}
				if( $results->rejected ){
					$this->out( '  - '.count( $results->rejected ).' messages rejected' );
					foreach( $results->rejected as $message ){
						$mailObject	= $this->logicMessage->getMessageObject( (int) $message->mailGroupMessageId );
						$this->out( '    Sender: '.$mailObject->getSender()->get() );
						$this->out( '    Subject: '.$mailObject->getSubject() );
					}
				}
			}
			catch( Throwable $t ){																	//  on throwable error or exception
				$this->logError( $t->getMessage() );
				$this->out( 'ERROR: '.$t->getMessage().' @ '.$t->getFile().':'.$t->getLine().PHP_EOL.$t->getTraceAsString() );
			}
//			$this->logicGroup->setGroupStatus( $groupId, Model_Mail_Group::STATUS_ACTIVATED );
			$this->logicGroup->setGroupStatus( $groupId, (int) $group->status );					//  restore old group status
		}
	}

	protected function __onInit(): void
	{
		$this->logicGroup		= Logic_Mail_Group::getInstance( $this->env );
		$this->logicMessage		= Logic_Mail_Group_Message::getInstance( $this->env );
		$this->logicMail		= Logic_Mail::getInstance( $this->env );
	}
}
