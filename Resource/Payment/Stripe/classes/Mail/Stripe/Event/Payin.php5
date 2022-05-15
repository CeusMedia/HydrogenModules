<?php
class Mail_Stripe_Event_Payin extends Mail_Abstract
{
	protected function generate(): self
	{
		$this->setSubject( 'Event: '. $this->data['event']->type );
		$this->setHtml( $this->renderHtml() );
		$this->setText( $this->renderText() );
		return $this;
	}

	protected function renderHtml(): string
	{
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'h4', 'PayIn' ),
			print_m( $this->data['payin'], NULL, NULL, TRUE, 'html' ),
			UI_HTML_Tag::create( 'h4', 'User' ),
			print_m( $this->data['user'], NULL, NULL, TRUE, 'html' ),
			UI_HTML_Tag::create( 'h4', 'Model Payin Data' ),
			print_m( $this->data['data'], NULL, NULL, TRUE, 'html' ),
		) );
	}

	protected function renderText(): string
	{
		$buffer	= new UI_OutputBuffer();
		print( View_Helper_Mail_Text::underscore( $this->data['event']->type, '=' ) );
		print( PHP_EOL.View_Helper_Mail_Text::underscore( 'Payin' ) );
		print_m( $this->data['payin'], NULL, NULL, FALSE, 'console' );
		print( PHP_EOL.View_Helper_Mail_Text::underscore( 'User' ) );
		print_m( $this->data['user'], NULL, NULL, FALSE, 'console' );
		print( PHP_EOL.View_Helper_Mail_Text::underscore( 'Payin Model Data' ) );
		print_m( $this->data['data'], NULL, NULL, FALSE, 'console' );
		return $buffer->get( TRUE );
	}
}
