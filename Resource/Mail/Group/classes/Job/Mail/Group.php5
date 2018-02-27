<?php
class Job_Mail_Group extends Job_Abstract{

	protected $logicGroup;
	protected $logicMail;
	protected $logicMessage;

	protected function __onInit(){
		$this->logicGroup		= new Logic_Mail_Group( $this->env );
		$this->logicMail		= new Logic_Mail( $this->env );
		$this->logicMessage		= new Logic_Mail_Group_Message( $this->env );
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
		$groups			= $this->logicGroup->getActiveGroups();
		foreach( $groups as $group ){
			$results	= $this->logicGroup->handleMailgroup( $group->mailGroupId );
			if( $results->mails ){
				$this->out( date( 'r' ).': Handling mailgroup: '.$group->title );
 				foreach( $results->mails as $mailId => $handledMail ){
//					if( !$handledMail->receivers )
//						continue;
					$this->out( '- Mail #'.$handledMail->id );
					$this->out( '  Sender: '.$handledMail->fromAddress );
					$this->out( '  Subject: '.$handledMail->subject );
					$this->out( '  Receivers: '.count( $handledMail->receivers ) );
					foreach( $handledMail->receivers as $receiver ){
						$this->out( '    To: '.$receiver->getAddress().' ('.$receiver->getName().')' );
					}
				}
			}
		}
	}
}
