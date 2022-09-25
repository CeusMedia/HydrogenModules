<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Stripe_EventUnverified extends Mail_Abstract
{
	protected function generate(): self
	{
		$data		= new Dictionary( $this->data );
		$buffer		= new UI_OutputBuffer();

		$event		= $data->get( 'event' );
		$entity		= $data->get( 'entity' );

		print HtmlTag::create( 'h2', 'Event verification failed' );
		print HtmlTag::create( 'h3', 'Event' );
		print print_m( $event, NULL, NULL, TRUE, 'html' );
		if( $entity instanceof Exception ){
			$e	= $entity;
			print HtmlTag::create( 'h3', 'Exception' );
			print HtmlTag::create( 'h4', 'Message / Code' );
			print HtmlTag::create( 'p', $e->getMessage().' ('.$e->getCode().')' );
			print HtmlTag::create( 'h4', 'File / Line' );
			print HtmlTag::create( 'p', $e->getFile().' @ '.$e->getLine() );
			print HtmlTag::create( 'h4', 'Trace' );
			print HtmlTag::create( 'pre', $e->getTraceAsString() );
		}
		else{
			print HtmlTag::create( 'h3', 'Entity' );
			print print_m( $entity, NULL, NULL, TRUE, 'html' );
		}
		print HtmlTag::create( 'h3', 'Info' );
		phpinfo( INFO_VARIABLES );
		$this->setSubject( 'Event verification failed' );
		$this->setHtml( $buffer->get( TRUE ) );
		return $this;
	}
}
