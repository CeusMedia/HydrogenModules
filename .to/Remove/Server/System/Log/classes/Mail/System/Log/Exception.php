<?php
class Mail_System_Log_Exception extends Mail_Abstract
{
	protected $helperFacts;

	protected function prepareFacts( $data ){
		$this->helperFacts	= new View_Helper_Mail_Exception_Facts( $this->env );
		$this->helperFacts->setException( $data['exception'] );
		if( !( isset( $data['showPrevious'] ) && !$data['showPrevious'] ) )
			$this->helperFacts->setShowPrevious( TRUE );
	}

	protected function generate(): self
	{
		$config		= $this->env->getConfig();
		$appName	= $config->get( 'app.name' );
		$data		= $this->data;
		$exception	= $data['exception'];

		$this->prepareFacts( $data );

		$this->setSubject( sprintf(
			'%s%s: %s',
			get_class( $exception ),
			$exception->getCode() ? ' ('.$exception->getCode().')' : '',
			$exception->getMessage()
		) );

		$html	= sprintf(
			'<h3>Exception <small class="muted">in %s</small></h3><h3>Facts</h3>%s</h3>Trace</h3>%s',
			$appName,
			$this->helperFacts->render(),
			UI_HTML_Exception_Trace::render( $exception )
		);
		$this->setHtml( $html );

		$root		= realpath( $this->env->uri ).'/';
		$plain		= join( PHP_EOL, array(
			View_Helper_Mail_Text::underscore( 'Exception' ),
			$this->helperFacts->renderAsText(),
			'',
			View_Helper_Mail_Text::underscore( 'Trace' ),
			str_replace( ' '.$root, ' ', $exception->getTraceAsString() ),
		) ).PHP_EOL;
		$this->setText( $plain );

		return $this;
	}
}
