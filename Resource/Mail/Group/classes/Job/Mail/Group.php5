<?php
class Job_Mail_Group extends Job_Abstract{

	public function handle(){
		$this->logic	= new Logic_Mail_Group( $this->env );
		$groups			= $this->logic->getActiveGroups();
		foreach( $groups as $group ){
			$results	= $this->logic->handleMailgroup( $group->mailGroupId, TRUE );
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
