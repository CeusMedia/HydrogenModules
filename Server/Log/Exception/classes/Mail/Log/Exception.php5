<?php
class Mail_Log_Exception extends Mail_Abstract{

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

		$this->page->addBody( '<h3><span class="muted">'.$appName.'</span> Exception</h3>' );
		$this->page->addBody( UI_HTML_Exception_View::render( $exception ) );
		if( $this->env->getModules()->has( 'UI_Bootstrap' ) )
			$this->addThemeStyle( 'bootstrap.min.css' );
		else
			$this->addThemeStyle( 'mail.min.css' );

		$this->setSubject( $subject );
		$this->addHtmlBody( $this->page->build() );
	}
}
?>
