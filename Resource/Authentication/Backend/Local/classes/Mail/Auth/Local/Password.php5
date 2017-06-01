<?php
class Mail_Auth_Local_Password extends Mail_Abstract{

	protected function generate( $data = array() ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$body	= $this->view->loadContentFile( 'mail/auth/local/password.txt', $data );
		$this->setSubject( $wordsMails['mails']['onPassword'] );
		$this->setText( $body );

		$body	= preg_replace( "/(http[\S]+)([.,])?/u", '<a href="\\1">\\1</a>\\2', $body );
		$this->setHtml( nl2br( $body ) );
	}
}
?>
