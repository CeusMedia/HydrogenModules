<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Work_Newsletter_Add extends Mail_Abstract
{
	protected function generate(): self
	{
		$words		= (object) $this->getWords( 'work/newsletter/reader', 'mail-add' );
		$prefix	= $this->env->getConfig()->get( 'module.resource_mail.subject.prefix' );
		$subject	= ( $prefix ? $prefix.' ' : '' ) . $words->mailSubject;
		$this->mail->setSubject( $subject );

		$this->setHtml( $this->renderHtmlBody() );
		$this->setText( $this->renderTextBody() );
		return $this;
	}

	protected function renderHtmlBody(): string
	{
		$data		= $this->data;
		$baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUri();

		$groups	= [];
		foreach( $data['groups'] as $item )
			$groups[]	= HtmlTag::create( 'li', $item->title );

		$words				= $this->getWords( 'work/newsletter/reader' );
		$data['salutation']	= $words['salutations'][$data['reader']->gender];
		$data['key']		= substr( md5( 'InfoNewsletterSalt:'.$data['readerId'] ), 10, 10 );
		$data['baseUrl']	= $baseUrl;
		$data['groups']		= HtmlTag::create( 'ul', $groups );
		$data['emailHash']	= base64_encode( $data['reader']->email );

		return $this->view->loadContentFile( 'mail/work/newsletter/add.html', $data );
	}

	protected function renderTextBody(): string
	{
		$data		= $this->data;
		$baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$baseUrl	= Logic_Frontend::getInstance( $this->env )->getUri();

		$groups	= [];
		foreach( $data['groups'] as $item )
			$groups[]	= '- '.$item->title;

		$words				= $this->getWords( 'work/newsletter/reader' );
		$data['salutation']	= $words['salutations'][$data['reader']->gender];
		$data['key']		= substr( md5( 'InfoNewsletterSalt:'.$data['readerId'] ), 10, 10 );
		$data['baseUrl']	= $baseUrl;
		$data['groups']		= join( "\n", $groups );
		$data['emailHash']	= base64_encode( $data['reader']->email );

		return $this->view->loadContentFile( 'mail/work/newsletter/add.txt', $data );
	}
}
