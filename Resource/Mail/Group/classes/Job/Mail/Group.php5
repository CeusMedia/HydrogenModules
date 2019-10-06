<?php
class Job_Mail_Group extends Job_Abstract{

	protected $logicGroup;
	protected $logicMail;
	protected $logicMessage;

	protected function __onInit(){
		$this->logicGroup		= Logic_Mail_Group::getInstance( $this->env );
		$this->logicMessage		= Logic_Mail_Group_Message::getInstance( $this->env );
		$this->logicMail		= Logic_Mail::getInstance( $this->env );
	}

	public function activateConfirmedMembers(){
		$modelMember	= new Model_Mail_Group_Member( $this->env );
		$modelAction	= new Model_Mail_Group_Action( $this->env );
		$modelUser		= new Model_User( $this->env );
		$groups			= $this->logicGroup->getGroups();
		$count			= 0;
		foreach( $groups as $group ){
			if( $group->type == Model_Mail_Group::TYPE_PUBLIC )
				continue;
			if( $group->type == Model_Mail_Group::TYPE_INVITE )
				continue;

			$indices	= array(
				'mailGroupId'	=> $group->mailGroupId,
				'status'		=> Model_Mail_Group_Member::STATUS_CONFIRMED,
			);
			$members	= $modelMember->getAllByIndices( $indices );
			if( $members )
				$this->out( 'Mail Group: '.$group->title );
			foreach( $members as $member ){
				$modelMember->edit( $member->mailGroupMemberId, array(
					'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
					'modifiedAt'	=> time(),
				) );

				$action	= $modelAction->getByIndices( array(
					'action'			=> 'activateAfterConfirm',
					'mailGroupId'		=> $group->mailGroupId,
					'mailGroupMemberId'	=> $member->mailGroupMemberId,
					'status'			=> 0,
				) );

				if( $action ){
					$count++;
					$manager	= $modelUser->get( $group->managerId );
					foreach( $this->logicGroup->getGroupMembers( $group->mailGroupId, TRUE ) as $entry ){
						if( $entry->address === $manager->email )
							continue;
						if( $entry->mailGroupMemberId === $member->mailGroupMemberId )
							continue;
						$mailData	= array(
							'member'	=> $member,
							'group'		=> $group,
							'greeting'	=> $action->message,
						);
						$receiver	= (object) array(
							'username'	=> $entry->title,
							'email'		=> $entry->address
						);
						$mail		= new Mail_Info_Mail_Group_Members_MemberJoined( $this->env, $mailData );
						$language	= $this->env->getLanguage()->getLanguage();
						$this->logicMail->handleMail( $mail, $receiver, $language );
					}
					$mail		= new Mail_Info_Mail_Group_Activated( $this->env, $mailData );
					$receiver	= (object) array(
						'username'	=> $member->title,
						'email'		=> $member->address
					);
					$language	= $this->env->getLanguage()->getLanguage();
					$this->logicMail->appendRegisteredAttachments( $mail, $language );
					$this->logicMail->handleMail( $mail, $receiver, $language );

					$modelAction->edit( $action->mailGroupActionId, array(
						'status'		=> Model_Mail_Group_Action::STATUS_HANDLED,
						'modifiedAt'	=> time(),
					) );
				}
				$this->out( '- Member "'.$member->title.'" <'.$member->address.'> activated' );
			}
		}
		$this->out( $count.' members activated' );
	}

	public function informMembersAboutNewMember(){
		$modelMember	= new Model_Mail_Group_Member( $this->env );
		$modelAction	= new Model_Mail_Group_Action( $this->env );
		$modelUser		= new Model_User( $this->env );
		$groups			= $this->logicGroup->getGroups();
		$count			= 0;
		foreach( $groups as $group ){
			$actions	= $modelAction->getAllByIndices( array(
				'action'			=> 'informAfterFirstActivate',
				'mailGroupId'		=> $group->mailGroupId,
				'status'			=> 0,
			) );
			if( $actions ){
				$manager	= $modelUser->get( $group->managerId );
				foreach( $actions as $action ){
					$member		= $modelMember->get( $action->mailGroupMemberId );
					foreach( $this->logicGroup->getGroupMembers( $group->mailGroupId, TRUE ) as $entry ){
						if( $entry->address === $manager->email )
							continue;
						if( $entry->mailGroupMemberId === $member->mailGroupMemberId )
							continue;
						$mailData	= array(
							'member'	=> $member,
							'group'		=> $group,
							'greeting'	=> $action->message,
						);
						$receiver	= (object) array(
							'username'	=> $entry->title,
							'email'		=> $entry->address
						);
						$mail		= new Mail_Info_Mail_Group_Members_MemberJoined( $this->env, $mailData );
						$language	= $this->env->getLanguage()->getLanguage();
						$this->logicMail->handleMail( $mail, $receiver, $language );
					}
					$mail		= new Mail_Info_Mail_Group_Activated( $this->env, $mailData );
					$receiver	= (object) array(
						'username'	=> $member->title,
						'email'		=> $member->address
					);
					$language	= $this->env->getLanguage()->getLanguage();
					$this->logicMail->appendRegisteredAttachments( $mail, $language );
					$this->logicMail->handleMail( $mail, $receiver, $language );

					$modelAction->edit( $action->mailGroupActionId, array(
						'status'		=> Model_Mail_Group_Action::STATUS_HANDLED,
						'modifiedAt'	=> time(),
					) );
				}
			}
		}
	}

	public function test(){
		$groups			= $this->logicGroup->getGroups();
		foreach( $groups as $group ){
			$this->out( '- Mail Group: '.$group->title );
			$results	= $this->logicMessage->importNewMails( $group->mailGroupId );
			$this->out( '  Imported ('.count( $results->mailsImported ).'): '.join( ',', $results->mailsImported ) );
			if( $results->errors ){
				$this->out( '  Errors:' );
				foreach( $results->errors as $error ){
					$this->out( '    - '.$error );
				}
			}
			$results	= $this->logicGroup->handleNewMails( $group->mailGroupId );
		}
	}

	public function handle(){
		if( $this->dryMode ){
			$this->out( 'DRY RUN - no changes will be made.' );
//			$this->out( 'Would send '.$count.' mails.' );
		}
		$groups		= $this->logicGroup->getActiveGroups();
		foreach( $groups as $group ){
			if( (int) $group->status === Model_Mail_Group::STATUS_WORKING )
				continue;
			$groupId	= $group->mailGroupId;
			$this->logicGroup->setGroupStatus( $groupId, Model_Mail_Group::STATUS_WORKING );
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
			} catch( Exception $e ){
				$this->logError( $e->getMessage() );
				$this->out( 'ERROR: '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine().PHP_EOL.$e->getTraceAsString() );
			}
			$this->logicGroup->setGroupStatus( $groupId, Model_Mail_Group::STATUS_ACTIVATED );
		}
	}
}
