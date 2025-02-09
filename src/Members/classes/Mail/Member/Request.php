<?php
class Mail_Member_Request extends Mail_Abstract
{
	protected function generate(): static
	{
		$data		= $this->data;
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$wordsMails	= $this->env->getLanguage()->getWords( 'member', 'mails' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
//		$data['from']		= $data['from'] ? '?from='.$data['from'] : '';
		$data['config']		= $this->env->getConfig()->getAll();
		$body	= $this->loadContentFile( 'mail/member/request.txt', $data ) ?? '';

		$this->setSubject( $wordsMails['mails']['onRequest'] );
		$this->setText( $body );

		$body	= preg_replace( "/(http[\S]+)([.,])?/u", '<a href="\\1">\\1</a>\\2', $body );
		$this->setHtml( nl2br( $body ) );
		return $this;
	}
}
