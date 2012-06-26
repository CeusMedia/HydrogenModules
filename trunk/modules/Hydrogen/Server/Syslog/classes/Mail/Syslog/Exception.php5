<?php
class Mail_Syslog_Exception extends Mail_Abstract{

	public function generate( $data = array() ){
		$config		= $this->env->getConfig();
		$appName	= $config->get( 'app.name' );
		$prefix		= trim( $config->get( 'module.resource_mail.subject.prefix' ) );
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
		$this->mail->setSubject( $subject );
		$this->mail->addBody( $body );
	}
}
?>