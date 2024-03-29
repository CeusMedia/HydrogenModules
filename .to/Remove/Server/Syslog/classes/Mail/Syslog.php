<?php

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\UI\HTML\PageFrame as HtmlPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Syslog extends Mail_Abstract
{
	protected function generate(): self
	{
		$data	= $this->data;
		$body	= '
<h3>ChatServer Syslog Mail</h3>
<dl>
	<dt>Subject</dt>
	<dd>'.$data['subject'].'</dd>
	<dt>From</dt>
	<dd>'.$data['sender'].'</dd>
	<dt>Date</dt
	><dd>'.date( "r").'</dd>
	<dt>Message</dt>
	<dd>
		<xmp class="code">
			'.htmlentities( $data['body'], ENT_COMPAT, 'UTF-8' ).'
		</xmp>
	</dd>
</dl>';

		$fileStyle	= $config->get( 'path.themes' ).'css/mail.min.css';
		$fileScript	= $config->get( 'path.scripts' ).'mail.min.js';
		$style		= file_exists( $fileStyle ) ? FileReader::load( $fileStyle ): '';
		$script		= file_exists( $fileScript ) ? FileReader::load( $fileScript ): '';

		$page	= new HtmlPage();
		$page->addHead( HtmlTag::create( 'style', $style ) );
		$page->addBody( $body );
		$page->addBody( HtmlTag::create( 'script', $script ) );

		$body->setContentEncoding( 'base64' );
		$this->mail->setSubject( $data['prefix']." Syslog Mail: ".$data['subject'] );
		$this->mail->setHtml( $page->build() );
		return $this;
	}
}
