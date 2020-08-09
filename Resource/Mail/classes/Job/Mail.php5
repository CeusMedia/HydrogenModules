<?php
class Job_Mail extends Job_Abstract{

	/**
	 *	Created test message and tries to send mail.
	 *	Allows to test direct mail sending using the transports of mail classes.
	 *	Allows to test queue based mail sending. Needs queue to be enabled in module config .
	 *	Supports verbose mode.
	 *	Does not Support dry mode.
	 *
	 *	Parameters:
	 *		--to=EMAIL_ADDRESS
	 *			- address of test mail receiver
	 *		--mode=MODE[,MODE]
	 *			- modes: direct, queue
	 *				- direct: send mail directly without queue
	 *				- queue: place test message in queue if enabled
	 *
	 *	@access		public
	 *	@return		void
	 */

	public function test(){
		$receiver	= $this->parameters->get( '--to' );
		$modes		= preg_split( '/\s*,\s*/s', $this->parameters->get( '--mode', 'direct' ) );

		$testDirectly	= in_array( 'direct', $modes );
		$testWithQueue	= in_array( 'queue', $modes );

		if( strlen( $receiver ) && !filter_var( $receiver, FILTER_VALIDATE_EMAIL ) ){
			$this->out( 'ERROR: Given email address is not valid.' );
			return;
		}

		if( !strlen( $receiver ) ){
			$appConfig	= $this->env->getConfig()->getAll( 'app.', TRUE );
			if( !$appConfig->get( 'admin.email' ) ){
				$this->out( 'SKIP: No admin mail address defined in config - please set app.admin.email in config.ini!' );
				return;
			}
			$receiver		= $appConfig->get( 'admin.email' );
		}


		$data	= array(
			'html'		=> $this->getMailContentAsHtml(),
			'text'		=> $this->getMailContentAsText(),
			'subject'	=> $this->getMailSubject(),
		);

		if( $this->verbose ){
			$this->out( 'Receiver: '.$receiver );
			$this->out( 'Subject:  '.$data['subject'] );
		}

		$mail	= new Mail_Test( $this->env, $data );
		if( $testDirectly ){
//			$mail->initTransport();                                                                     //  override serialized mail transfer
			try{
				$mail->sendTo( array( 'email' => $receiver ) );
				$this->out( 'OK: Mail sent.' );
	//		$mailbox	= new CeusMedia\Mail\Mailbox();
			}
			catch( Exception $e ){
				$this->logException( $e );
				$this->out( 'ERROR: Sending mail failed: '.$e->getMessage() );
			}
		}

		if( $testWithQueue ){
			$logicMail	= $this->env->getLogic()->get( 'Mail' );
			$language	= $this->env->getLanguage()->getLanguage();
			try{
				$result		= $logicMail->handleMail( $mail, array( 'email' => $receiver ), $language );
				if( $result ){
					$this->out( 'OK: Mail enqueued.' );
				}
				else{
					$this->logError( 'Adding mail to queue failed' );
					$this->out( 'FAIL: Mail not enqueued.' );
				}
			}
			catch( Exception $e ){
				$this->logException( $e );
				$this->out( 'ERROR: Mail to queue failed: '.$e->getMessage() );
			}
		}
	}

	//  --  PROTECTED  --  //
	protected function getMailContentAsHtml(){
		return 'Test-Mail '.date( 'y-m-d / H:i' );
	}

	protected function getMailContentAsText(){
		return 'Test-Mail '.date( 'y-m-d / H:i' );
	}

	protected function getMailSubject(){
		$subject		= 'Test-Mail '.date( 'y-m-d / H:i' );
		$subjectConfig	= $this->env->getConfig()->getAll( 'module.resource_mail.subject.', TRUE );
		if( !$subjectConfig->get( 'prefix' ) && !$subjectConfig->get( 'template' ) ){
			$appConfig	= $this->env->getConfig()->getAll( 'app.', TRUE );
			$appTitle	= $appConfig->get( 'title', $appConfig->get( 'name' ) );
			$prefix		= $appTitle ? '['.( $appTitle ).'] ' : '';
			$subject	= $prefix.$subject;
		}
		return $subject;
	}
}
