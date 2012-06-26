<?php
class Mail_Syslog extends Mail_Abstract{

	public function generate( $data = array() ){
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

		$fileStyle	= File_Reader::load( $config->get( 'path.themes' ).'css/mail.min.css' );
		$fileScript	= File_Reader::load( $config->get( 'path.scripts' ).'mail.min.js' );
		$style	= file_exists( $fileStyle ) ? File_Reader::load( $fileStyle ): '';
		$style	= file_exists( $fileScript ) ? File_Reader::load( $fileScript ): '';
		
		$page	= new UI_HTML_PageFrame();
		$page->addHead( UI_HTML_Tag::create( 'style', $style ) );
		$page->addBody( $body );
		$page->addBody( UI_HTML_Tag::create( 'script', $script ) );
		
		$body	= new Net_Mail_Body( base64_encode( $page->build() ), Net_Mail_Body::TYPE_HTML );
		$body->setContentEncoding( 'base64' );
		$this->mail->setSubject( $data['prefix']." Syslog Mail: ".$data['subject'] );
		$this->mail->addBody( $body );
	}
}
?>