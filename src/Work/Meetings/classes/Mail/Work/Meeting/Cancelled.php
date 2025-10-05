<?php
class Mail_Work_Meeting_Cancelled extends Mail_Abstract
{
	/**
	 *	@return		static
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		$words		= (object) $this->getWords( 'work/meeting', 'mail-cancelled' );
		$prefix		= $this->env->getConfig()->get( 'module.resource_mail.subject.prefix' );
		$subject	= ( $prefix ? $prefix.' ' : '' ) . $words->subject;
		$this->mail->setSubject( sprintf( $subject, $this->data['meeting']->title ) );

		$baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUrl();
		$this->data['baseUrl']	= $baseUrl;

		$this->setHtml( $this->renderHtmlBody() );
		$this->setText( $this->renderTextBody() );
		return $this;
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function renderHtmlBody(): string
	{
		$data		= $this->data;
		$words		= $this->getWords( 'work/meeting' );

		return $this->loadContentFile( 'mail/work/meeting/cancelled.html', $data ) ?? '';
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function renderTextBody(): string
	{
		$data		= $this->data;
		$words		= $this->getWords( 'work/meeting' );

		return $this->loadContentFile( 'mail/work/meeting/cancelled.txt', $data ) ?? '';
	}
}
