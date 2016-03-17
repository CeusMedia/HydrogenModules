<?php
class Mail_Auth_Password extends Mail_Abstract{

	protected function generate( $data = array() ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$body	= $this->view->loadContentFile( 'mail/auth/local/password.txt', $data );
		$this->setSubject( $wordsMails['mails']['onRegister'] );
		$this->addTextBody( $body );
	}
}
?>
