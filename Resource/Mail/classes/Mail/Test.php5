<?php
class Mail_Test extends Mail_Abstract{

	public function generate( $data = array() ){
		$this->setSubject( 'Test' );

		$text		= strip_tags( $this->renderBody( $data ) );
		$text		= chunk_split( base64_encode( $text ), 78 );
		$mailText	= new Net_Mail_Body( $text, Net_Mail_Body::TYPE_PLAIN );
		$mailText->setContentEncoding( 'base64' );
		$this->mail->addBody( $mailText );

		$html		= $this->renderBody( $data );
		$html		= chunk_split( base64_encode( $html ), 78 );
		$mailBody	= new Net_Mail_Body( $html, Net_Mail_Body::TYPE_HTML );
		$mailBody->setContentEncoding( 'base64' );
		$this->mail->addBody( $mailBody );
	}

	public function renderBody( $data = array() ){
		if( isset( $data['body'] ) && strlen( trim( $data['body'] ) ) )
			return $data['body'];
		return "Test: ".time();
	}
}
?>
