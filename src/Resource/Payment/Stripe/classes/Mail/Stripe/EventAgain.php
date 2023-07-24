<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\UI\OutputBuffer;

class Mail_Stripe_EventAgain extends Mail_Abstract
{
	protected function generate(): self
	{
		$data		= new Dictionary( $this->data );
		$buffer		= new OutputBuffer();
		$event		= $data->get( 'event' );
		print HtmlTag::create( 'h2', 'Attempt to add duplicate event' );
		print HtmlTag::create( 'h3', 'Event' );
		print print_m( $event, NULL, NULL, TRUE, 'html' );
		print HtmlTag::create( 'h3', 'Info' );
		phpinfo( INFO_VARIABLES );
		$this->setSubject( 'Attempt to add duplicate event' );
		$this->setHtml( $buffer->get( TRUE ) );
		return $this;
	}
}
