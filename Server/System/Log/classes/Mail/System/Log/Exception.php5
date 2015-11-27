<?php
class Mail_System_Log_Exception extends Mail_Abstract{

	public function generate( $data = array() ){
		$config		= $this->env->getConfig();
		$appName	= $config->get( 'app.name' );
		$exception	= $data['exception'];

		$subject	= 'Exception: '.$exception->getMessage();
		if( $exception->getCode() )
			$subject	.= ' ('.$exception->getCode().')';
		$this->setSubject( $subject );

		$body	= '<h3>Exception <small class="muted">'.$appName.'</small></h3>'.UI_HTML_Exception_View::render( $exception );
		$this->addThemeStyle( 'bootstrap.min.css' );
		$this->addThemeStyle( 'mail.min.css' );
		$this->page->addBody( $body );

		$this->addHtmlBody( $this->page->build() );
	}
}
?>
