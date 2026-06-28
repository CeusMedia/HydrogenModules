<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_Newsletter extends Hook
{
	/**
	 * @return        void
	 * @throws        ReflectionException
	 */
	public function onMailSent(): void
	{
		$mailId	= trim( $this->getPayload()['mailId'] ?? '' );
		if( '' === $mailId )
			return;

		$modelMail	= new Model_Mail( $this->env );
		/** @var ?Entity_Mail $mail */
		$mail		= $modelMail->get( $mailId );
		if( NULL === $mail )
			return;
		if( Model_Mail::STATUS_SENT > $mail->status )
			return;

		$modelLetter	= new Model_Newsletter_Reader_Letter( $this->env );
		$modelLetter->editByIndices( ['mailId' => $mailId], [
			'status'	=> Model_Newsletter_Reader_Letter::STATUS_SENT,
			'sentAt'	=> time(),
			'modified'	=> time(),
		] );

	}
}