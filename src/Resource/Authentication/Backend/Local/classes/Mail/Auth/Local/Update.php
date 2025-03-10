<?php

use CeusMedia\Common\Alg\ID;
use CeusMedia\Common\Exception\IO as IoException;

class Mail_Auth_Local_Update extends Mail_Abstract
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

		$this->setSubject( $wordsMails['mails']['onUpdate'] );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$data['hash']		= sha1( join( '-', [$data['userId'], $data['passwordId']] ) );
		$plain	= $this->loadContentFile( 'mail/auth/local/update.txt', $data ) ?? '';
		$this->setText( $plain );

		$html	= preg_replace( "/(http[\S]+)([.,])?/u", '<a href="\\1">\\1</a>\\2', $plain );
		$html	= nl2br( $html );
		$this->setHtml( $html );
		return $this;
	}
}
