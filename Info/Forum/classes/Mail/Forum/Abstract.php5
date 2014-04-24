<?php
abstract class Mail_Forum_Abstract extends Mail_Abstract{

	protected $modelPost;
	protected $modelThread;
	protected $model;
	
	protected function generate( $data = array() ){
		$this->modelPost	= new Model_Forum_Post( $this->env );
		$this->modelThread	= new Model_Forum_Thread( $this->env );
		$this->modelTopic	= new Model_Forum_Topic( $this->env );
		
		$html		= $this->renderBody( $data );
		$body		= chunk_split( base64_encode( $html ), 78 );
		$mailBody	= new Net_Mail_Body( $body, Net_Mail_Body::TYPE_HTML );
		$mailBody->setContentEncoding( 'base64' );
		$this->mail->addBody( $mailBody );
	}
}
?>