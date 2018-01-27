<?php
class Mail_Stripe_EventAgain extends Mail_Abstract{

	protected function generate( $data = array() ){
		$data		= new ADT_List_Dictionary( $this->data );
		$buffer		= new UI_OutputBuffer();
		$event		= $data->get( 'event' );
		print UI_HTML_Tag::create( 'h2', 'Attempt to add duplicate event' );
		print UI_HTML_Tag::create( 'h3', 'Event' );
		print print_m( $event, NULL, NULL, TRUE, 'html' );
		print UI_HTML_Tag::create( 'h3', 'Info' );
		phpinfo( INFO_VARIABLES );
		$this->setSubject( 'Attempt to add duplicate event' );
		$this->setHtml( $buffer->get( TRUE ) );
	}
}
?>