<?php
class Mail_Auth_Register extends Mail_Abstract
{
	/**
	 *	Generate mail bodies (HTML and/or plaintext, if implemented).
	 *	@access		protected
	 *	@return		self
	 *	@todo		upgrade to use CeusMedia::Mail
	 */
	protected function generate(): self
	{
		$words					= $this->env->getLanguage()->getWords( 'auth', 'mails' );
		$this->data['config']	= $this->env->getConfig()->getAll();
		$prefix					= $this->env->getConfig()->get( 'module.resource_mail.prefix' );

		$subject	= ( $prefix ? $prefix.' ' : '' ).$words['mails']['onRegister'];
		$content	= $this->view->loadContentFile( 'mail/auth/register', $this->data );

		$this->mail->setSubject( $subject );
		$this->mail->setText( $content );
		return $this;
	}
}
