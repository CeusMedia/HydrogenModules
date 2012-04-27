<?php
class Mail_Auth_Register extends Mail_Abstract{

	protected function generate( $data = array() ){
		$words			= $this->env->getLanguage()->getWords( 'auth', 'mails' );
		$data['config']	= $this->env->getConfig()->getAll();

		$subject		= $words['mails']['onRegister'];
		$body			= $this->view->loadContentFile( 'mails/auth/register', $data );

		$this->mail->setSubject( $subject );
		$this->mail->addBody( new Net_Mail_Body( $body, Net_Mail_Body::TYPE_PLAIN ) );
	}
}
?>
