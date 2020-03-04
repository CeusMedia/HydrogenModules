<?php
class Job_Mail extends Job_Abstract{

	public function test(){
		$appConfig	= $this->env->getConfig()->getAll( 'app.', TRUE );

		if( !$appConfig->get( 'admin.email' ) ){
			$this->out( 'SKIP: No admin mail address defined in config' );
			return;
		}
		$receiver		= $appConfig->get( 'admin.email' );
		$testDirectly	= TRUE;
		$testWithQueue	= !TRUE;

		$data	= array(
			'html'		=> $this->getMailContentAsHtml(),
			'text'		=> $this->getMailContentAsText(),
			'subject'	=> $this->getMailSubject(),
		);

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
