<?php
class Mail_Info_Newsletter_Register extends Mail_Abstract
{
	/**
	 *	@return		static
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		$words		= (object) $this->getWords( 'info/newsletter', 'register' );
		$prefix	= $this->env->getConfig()->get( 'module.resource_mail.subject.prefix' );
		$subject	= ( $prefix ? $prefix.' ' : '' ) . $words->mailSubject;
		$this->mail->setSubject( $subject );

		$this->setText( $this->renderTextBody() );
		return $this;
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function renderTextBody(): string
	{
		$data				= $this->data;
		$words				= $this->getWords( 'info/newsletter' );
		$data['salutation']	= $words['salutations'][$data['reader']->gender];
		$data['key']		= substr( md5( 'InfoNewsletterSalt:'.$data['readerId'] ), 10, 10 );
		$data['baseUrl']	= $this->env->url;

		return $this->loadContentFile( 'mail/info/newsletter/register.txt', $data ) ?? '';
	}
}
