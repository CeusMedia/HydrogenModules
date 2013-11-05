<?php
class Mail_Auth_Register extends Mail_Abstract{

	protected function generate( $data = array() ){
		$words			= $this->env->getLanguage()->getWords( 'auth', 'mails' );
		$data['config']	= $this->env->getConfig()->getAll();
		$prefix			= $this->env->getConfig()->get( 'module.resource_mail.prefix' );

		$subject	= ( $prefix ? $prefix.' ' : '' ).$words['mails']['onRegister'];
		$content	= $this->view->loadContentFile( 'mail/auth/register', $data );
		$body		= new Net_Mail_Body( base64_encode( $content ), Net_Mail_Body::TYPE_PLAIN );
		$body->setContentEncoding( "base64" );

		$this->mail->setSubject( $subject );
		$this->mail->addBody( $body );
	}
}
?>
