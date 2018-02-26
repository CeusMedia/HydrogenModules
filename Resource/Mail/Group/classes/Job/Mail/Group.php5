<?php
class Job_Mail_Group extends Job_Abstract{

	public function activateConfirmedMembers(){
		$logic			= new Logic_Mail_Group( $this->env );
		$logicMail		= new Logic_Mail( $this->env );
		$modelMember	= new Model_Mail_Group_Member( $this->env );
		$modelAction	= new Model_Mail_Group_Action( $this->env );
		$modelUser		= new Model_User( $this->env );
		$groups			= $logic->getGroups();
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
					foreach( $logic->getGroupMembers( $group->mailGroupId, TRUE ) as $entry ){
						if( $entry->address === $manager->email )
							continue;
						if( $entry->mailGroupMemberId === $member->mailGroupMemberId )
							continue;
						$mailData	= array(
							'member'	=> $member,
							'group'		=> $group,
							'greeting'	=> $action->message,
						);
						$logicMail->handleMail(
							new Mail_Info_Mail_Group_Members_MemberJoined( $this->env, $mailData ),
							(object) array( 'email' => $entry->address ),
							$this->env->getLanguage()->getLanguage()
						);
					}

					$mail		= new Mail_Info_Mail_Group_Activated( $this->env, $mailData );
					$receiver	= (object) array(
						'username'	=> $member->title,
						'email'		=> $member->address
					);
					$language	= $this->env->getLanguage()->getLanguage();
					$logicMail->appendRegisteredAttachments( $mail, $language );
					$logicMail->handleMail( $mail, $receiver, $language );

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
		$logic			= new Logic_Mail_Group( $this->env );
		$logicMail		= new Logic_Mail( $this->env );
		$modelMember	= new Model_Mail_Group_Member( $this->env );
		$modelAction	= new Model_Mail_Group_Action( $this->env );
		$modelUser		= new Model_User( $this->env );
		$groups			= $logic->getGroups();
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
					foreach( $logic->getGroupMembers( $group->mailGroupId, TRUE ) as $entry ){
						if( $entry->address === $manager->email )
							continue;
						if( $entry->mailGroupMemberId === $member->mailGroupMemberId )
							continue;
						$mailData	= array(
							'member'	=> $member,
							'group'		=> $group,
							'greeting'	=> $action->message,
						);
						$logicMail->handleMail(
							new Mail_Info_Mail_Group_Members_MemberJoined( $this->env, $mailData ),
							(object) array( 'email' => $entry->address ),
							$this->env->getLanguage()->getLanguage()
						);
					}
					$mail		= new Mail_Info_Mail_Group_Activated( $this->env, $mailData );
					$receiver	= (object) array(
						'username'	=> $member->title,
						'email'		=> $member->address
					);
					$language	= $this->env->getLanguage()->getLanguage();
					$logicMail->appendRegisteredAttachments( $mail, $language );
					$logicMail->handleMail( $mail, $receiver, $language );

					$modelAction->edit( $action->mailGroupActionId, array(
						'status'		=> Model_Mail_Group_Action::STATUS_HANDLED,
						'modifiedAt'	=> time(),
					) );
				}
			}
		}
	}

	public function test(){
		$logic		= new Logic_Mail_Group( $this->env );
		$groups		= $logic->getGroups();
		foreach( $groups as $group ){
			$this->out( '- Mail Group: '.$group->title );
			$results	= $logic->importNewMails( $group->mailGroupId );
			$this->out( '  Imported ('.count( $results->mailsImported ).'): '.join( ',', $results->mailsImported ) );
			if( $results->errors ){
				$this->out( '  Errors:' );
				foreach( $results->errors as $error ){
					$this->out( '    - '.$error );
				}
			}
			$results	= $logic->handleNewMails( $group->mailGroupId );
		}
	}

	public function handle(){
		$logic		= new Logic_Mail_Group( $this->env );
		$groups		= $logic->getActiveGroups();
		foreach( $groups as $group ){
			$results	= $logic->handleMailgroup( $group->mailGroupId );
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
