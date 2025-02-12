<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\UI\OutputBuffer;

class Mail_Stripe_EventFailed extends Mail_Abstract
{
	/**
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		$data		= new Dictionary( $this->data );
		$buffer		= new OutputBuffer();
		print HtmlTag::create( 'h2', 'Error on handling event' );
		if( $data->get( 'eventId' ) ){
			$model		= new Model_Stripe_Event( $this->env );
			$event		= $model->get( $data->get( 'eventId' ) );
			print HtmlTag::create( 'h3', 'Event' );
			print print_m( $event, NULL, NULL, TRUE, 'html' );
		}
		if( $data->get( 'exception' ) instanceof Exception ){
			$e	= $data->get( 'exception' );
			print HtmlTag::create( 'h3', 'Exception' );
			print HtmlTag::create( 'h4', 'Message / Code' );
			print HtmlTag::create( 'p', $e->getMessage().' ('.$e->getCode().')' );
			print HtmlTag::create( 'h4', 'File / Line' );
			print HtmlTag::create( 'p', $e->getFile().' @ '.$e->getLine() );
			print HtmlTag::create( 'h4', 'Trace' );
			print HtmlTag::create( 'pre', $e->getTraceAsString() );
		}
		print HtmlTag::create( 'h3', 'Info' );
		phpinfo( INFO_VARIABLES );
		$this->setSubject( 'Event handling failed' );
		$this->setHtml( $buffer->get( TRUE ) );
		return $this;
	}
}
