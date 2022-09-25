<?php
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
		$fileStyle	= FS_File_Reader::load( $config->get( 'path.themes' ).'css/mail.min.css' );
		$fileScript	= FS_File_Reader::load( $config->get( 'path.scripts' ).'mail.min.js' );
		$style	= file_exists( $fileStyle ) ? FS_File_Reader::load( $fileStyle ): '';
		$script	= file_exists( $fileScript ) ? FS_File_Reader::load( $fileScript ): '';

		$page	= new UI_HTML_PageFrame();
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
