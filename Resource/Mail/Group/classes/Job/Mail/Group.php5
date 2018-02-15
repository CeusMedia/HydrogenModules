<?php
class Job_Mail_Group extends Job_Abstract{

	public function activateConfirmedMembers(){
		$this->logic	= new Logic_Mail_Group( $this->env );
		$logicMail		= new Logic_Mail( $this->env );
		$modelMember	= new Model_Mail_Group_Member( $this->env );
		$groups			= $this->logic->getGroups();
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
			$count		+= count( $members );
			foreach( $members as $member ){
				$modelMember->edit( $member->mailGroupMemberId, array(
					'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
					'modifiedAt'	=> time(),
				) );
				$logicMail->handleMail(
					new Mail_Info_Mail_Group_Activated( $this->env, $mailData ),
					(object) array( 'email' => $member->address ),
					$this->env->getLanguage()->getLanguage()
				);
				$this->out( '- Member "'.$member->title.'" <'.$member->address.'> activated' );
			}
		}
		$this->out( $count.' members activated' );
	}

	public function test(){
		$this->logic	= new Logic_Mail_Group( $this->env );
		$groups			= $this->logic->getGroups();
		foreach( $groups as $group ){
			$this->out( '- Mail Group: '.$group->title );
			$results	= $this->logic->importNewMails( $group->mailGroupId );
			$this->out( '  Imported ('.count( $results->mailsImported ).'): '.join( ',', $results->mailsImported ) );
			if( $results->errors ){
				$this->out( '  Errors:' );
				foreach( $results->errors as $error ){
					$this->out( '    - '.$error );
				}
			}
			$results	= $this->logic->handleNewMails( $group->mailGroupId );
		}
	}

	public function handle(){
		$this->logic	= new Logic_Mail_Group( $this->env );
		$groups			= $this->logic->getActiveGroups();
		foreach( $groups as $group ){
			$results	= $this->logic->handleMailgroup( $group->mailGroupId );
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
