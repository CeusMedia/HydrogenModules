<?php
class Mail_Member_Request extends Mail_Abstract{

	protected function generate( $data = array() ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$wordsMails	= $this->env->getLanguage()->getWords( 'member', 'mails' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
//		$data['from']		= $data['from'] ? '?from='.$data['from'] : '';
		$data['config']		= $this->env->getConfig()->getAll();
		$body	= $this->view->loadContentFile( 'mail/member/request.txt', $data );

		$this->setSubject( $wordsMails['mails']['onRequest'] );
		$this->addTextBody( $body );
	}
}
?>
