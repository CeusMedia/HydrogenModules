<?php
class Mail_Auth_Register extends Mail_Abstract{

	protected function generate( $data = array() ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$wordsMails	= $this->env->getLanguage()->getWords( 'auth', 'mails' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['from']		= $data['from'] ? '?from='.$data['from'] : '';
		$data['config']		= $this->env->getConfig()->getAll();
		$body	= $this->view->loadContentFile( 'mail/auth/register.txt', $data );

		$this->setSubject( $wordsMails['mails']['onRegister'] );
		$this->addTextBody( $body );
	}
}
?>
