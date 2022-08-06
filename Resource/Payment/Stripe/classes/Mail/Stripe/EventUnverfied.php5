<?php

use CeusMedia\Common\ADT\Collection\Dictionary;

class Mail_Stripe_EventUnverfied extends Mail_Abstract
{
	protected function generate(): self
	{
		$data		= new Dictionary( $this->data );
		$buffer		= new UI_OutputBuffer();

		$event		= $data->get( 'event' );
		$entity		= $data->get( 'entity' );

		print UI_HTML_Tag::create( 'h2', 'Event verification failed' );
		print UI_HTML_Tag::create( 'h3', 'Event' );
		print print_m( $event, NULL, NULL, TRUE, 'html' );
		if( $entity instanceof Exception ){
			$e	= $entity;
			print UI_HTML_Tag::create( 'h3', 'Exception' );
			print UI_HTML_Tag::create( 'h4', 'Message / Code' );
			print UI_HTML_Tag::create( 'p', $e->getMessage().' ('.$e->getCode().')' );
			print UI_HTML_Tag::create( 'h4', 'File / Line' );
			print UI_HTML_Tag::create( 'p', $e->getFile().' @ '.$e->getLine() );
			print UI_HTML_Tag::create( 'h4', 'Trace' );
			print UI_HTML_Tag::create( 'pre', $e->getTraceAsString() );
		}
		else{
			print UI_HTML_Tag::create( 'h3', 'Entity' );
			print print_m( $entity, NULL, NULL, TRUE, 'html' );
		}
		print UI_HTML_Tag::create( 'h3', 'Info' );
		phpinfo( INFO_VARIABLES );
		$this->setSubject( 'Event verification failed' );
		$this->setHtml( $buffer->get( TRUE ) );
		return $this;
	}
}
