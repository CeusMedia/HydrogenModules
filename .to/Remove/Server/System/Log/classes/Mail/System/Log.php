<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/*class Mail_System_Log extends Mail_Abstract{

	public function generate( $data = [] ){
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
		$style		= file_exists( $fileStyle ) ? FS_File_Reader::load( $fileStyle ): '';
		$script		= file_exists( $fileScript ) ? FS_File_Reader::load( $fileScript ): '';
		
		$page	= new UI_HTML_PageFrame();
		$page->addHead( HtmlTag::create( 'style', $style ) );
		$page->addBody( $body );
		$page->addBody( HtmlTag::create( 'script', $script ) );
		
		$body	= new Net_Mail_Body( base64_encode( $page->build() ), Net_Mail_Body::TYPE_HTML );
		$body->setContentEncoding( 'base64' );
		$this->mail->setSubject( $data['prefix']." Syslog Mail: ".$data['subject'] );
		$this->mail->addBody( $body );
	}
}*/
?>
