<?php

use CeusMedia\Common\UI\HTML\Exception\Trace as HtmlExceptionTrace;

class Mail_Log_Exception extends Mail_Abstract
{
	/** @var View_Helper_Mail_Exception_Facts|NULL $helperFacts */
	protected ?View_Helper_Mail_Exception_Facts $helperFacts	= NULL;

	/**
	 *	@param		$data
	 *	@return		void
	 */
	protected function prepareFacts( $data ): void
	{
		$this->helperFacts	= new View_Helper_Mail_Exception_Facts( $this->env );
		$this->helperFacts->setException( $data['exception'] );
		if( !( isset( $data['showPrevious'] ) && !$data['showPrevious'] ) )
			$this->helperFacts->setShowPrevious();
	}

	/**
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): self
	{
		$config		= $this->env->getConfig();
		$appName	= $config->get( 'app.name' );
		$exception	= $this->data['exception'];

		$this->prepareFacts( $this->data );

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
			HtmlExceptionTrace::render( $exception )
		);
		$this->setHtml( $html );

		$root		= realpath( $this->env->uri ).'/';
		$this->setText(
			View_Helper_Mail_Text::underscore( 'Exception' ).PHP_EOL.
			$this->helperFacts->renderAsText().PHP_EOL.
			PHP_EOL.
			View_Helper_Mail_Text::underscore( 'Trace' ).PHP_EOL.
			str_replace( ' '.$root, ' ', $exception->getTraceAsString() ).PHP_EOL
		);
		return $this;
	}
}
