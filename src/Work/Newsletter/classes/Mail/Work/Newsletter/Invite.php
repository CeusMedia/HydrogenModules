<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Work_Newsletter_Invite extends Mail_Abstract
{
	/**
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		$words		= (object) $this->getWords( 'work/newsletter/reader', 'mail-invite' );
		$prefix		= $this->env->getConfig()->get( 'module.resource_mail.subject.prefix' );
		$subject	= ( $prefix ? $prefix.' ' : '' ) . $words->mailSubject;
		$this->mail->setSubject( $subject );

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
		$baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUrl();

		$groups	= [];
		foreach( $data['groups'] as $item )
			$groups[]	= HtmlTag::create( 'li', $item->title );

		$words				= $this->getWords( 'work/newsletter/reader' );
		$data['salutation']	= $words['salutations'][$data['reader']->gender];
		$data['key']		= substr( md5( 'InfoNewsletterSalt:'.$data['readerId'] ), 10, 10 );
		$data['baseUrl']	= $baseUrl;
		$data['groups']		= HtmlTag::create( 'ul', $groups );
		$data['emailHash']	= base64_encode( $data['reader']->email );

		return $this->loadContentFile( 'mail/work/newsletter/invite.html', $data ) ?? '';
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function renderTextBody(): string
	{
		$data		= $this->data;
		$baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUrl();

		$groups	= [];
		foreach( $data['groups'] as $item )
			$groups[]	= '- '.$item->title;

		$words				= $this->getWords( 'work/newsletter/reader' );
		$data['salutation']	= $words['salutations'][$data['reader']->gender];
		$data['key']		= substr( md5( 'InfoNewsletterSalt:'.$data['readerId'] ), 10, 10 );
		$data['baseUrl']	= $baseUrl;
		$data['groups']		= join( "\n", $groups );
		$data['emailHash']	= base64_encode( $data['reader']->email );

		return $this->loadContentFile( 'mail/work/newsletter/invite.txt', $data ) ?? '';
	}
}
