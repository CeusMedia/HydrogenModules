<?php
class Mail_Member_Revoke extends Mail_Abstract{

	protected function generate( $data = array() ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$wordsMails	= $this->env->getLanguage()->getWords( 'member', 'mails' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
//		$data['from']		= $data['from'] ? '?from='.$data['from'] : '';
		$data['config']		= $this->env->getConfig()->getAll();
		$body	= $this->view->loadContentFile( 'mail/member/revoke.txt', $data );

		$this->setSubject( $wordsMails['mails']['onRevoke'] );
		$this->setText( $body );

		$body	= preg_replace( "/(http[\S]+)([.,])?/u", '<a href="\\1">\\1</a>\\2', $body );
		$this->setHtml( nl2br( $body ) );
		$this->setHtml( $body );
	}
}
?>
