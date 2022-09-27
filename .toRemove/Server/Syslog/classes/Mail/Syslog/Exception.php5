<?php
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\UI\HTML\PageFrame as HtmlPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Syslog_Exception extends Mail_Abstract
{
	protected function generate(): self
	{
		$config		= $this->env->getConfig();
		$appName	= $config->get( 'app.name' );
		$prefix		= trim( $config->get( 'module.resource_mail.subject.prefix' ) );
		$data		= $this->data;
		$exception	= $data['exception'];

		$subject	= 'Exception: '.$exception->getMessage();
		if( $exception->getCode() )
			$subject	.= ' ('.$exception->getCode().')';
		if( $prefix )
			$subject	= $prefix.' '.$subject;

		$body	= '
<h3>'.$appName.' Exception</h3>
'.UI_HTML_Exception_View::render( $exception ).'
';
		$fileStyle	= FileReader::load( $config->get( 'path.themes' ).'css/mail.min.css' );
		$fileScript	= FileReader::load( $config->get( 'path.scripts' ).'mail.min.js' );
		$style	= file_exists( $fileStyle ) ? FileReader::load( $fileStyle ): '';
		$script	= file_exists( $fileScript ) ? FileReader::load( $fileScript ): '';

		$page	= new HtmlPage();
		$page->addHead( HtmlTag::create( 'style', $style ) );
		$page->addBody( $body );
		$page->addBody( HtmlTag::create( 'script', $script ) );

		$body	= new Net_Mail_Body( base64_encode( $page->build() ), Net_Mail_Body::TYPE_HTML );
		$body->setContentEncoding( 'base64' );
		$this->mail->setSubject( $subject );
		$this->mail->addBody( $body );
		return $this;
	}
}
