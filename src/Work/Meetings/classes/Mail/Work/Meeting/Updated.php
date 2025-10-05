<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Work_Meeting_Updated extends Mail_Abstract
{
	/**
	 *	@return		static
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		$words		= (object) $this->getWords( 'work/meeting', 'mail-updated' );
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

		/** @var Entity_Work_Meeting $meeting */
		$meeting	= $this->data['meeting'];
		foreach( $this->data['changes'] as $column => $change ){
			$meeting->set( $column, join( ' ', [
				HtmlTag::create( 'del', $change[0] ),
				HtmlTag::create( 'ins', $change[1] ),
			] ) );
		}

		return $this->loadContentFile( 'mail/work/meeting/updated.html', $data ) ?? '';
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function renderTextBody(): string
	{
		$data		= $this->data;
		$data['meeting']->content	= strip_tags( $data['meeting']->content );

		$words		= $this->getWords( 'work/meeting' );

		return $this->loadContentFile( 'mail/work/meeting/updated.txt', $data ) ?? '';
	}
}
