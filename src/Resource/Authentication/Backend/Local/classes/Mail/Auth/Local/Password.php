<?php

use CeusMedia\Common\Exception\IO as IoException;

class Mail_Auth_Local_Password extends Mail_Abstract
{
	/**
	 *	@return		static
	 *	@throws		ReflectionException
	 *	@throws		IoException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		$data		= $this->data;
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$this->setSubject( $wordsMails['mails']['onPassword'] );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$plain	= $this->loadContentFile( 'mail/auth/local/password.txt', $data ) ?? '';
		$this->setText( $plain );

		$html	= preg_replace( "/(http[\S]+)([.,])?/u", '<a href="\\1">\\1</a>\\2', $plain );
		$html	= nl2br( $html );
		$this->setHtml( $html );
		return $this;
	}
}
