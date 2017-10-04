<?php
class Mail_Mangopay_EventFailed extends Mail_Abstract{

	protected function generate( $data = array() ){
		$data		= new ADT_List_Dictionary( $this->data );
		$buffer		= new UI_OutputBuffer();
		print UI_HTML_Tag::create( 'h2', 'Error on handling event' );
		if( $data->get( 'eventId' ) ){
			$model		= new Model_Mangopay_Event( $this->env );
			$event		= $model->get( $data->get( 'eventId' ) );
			print UI_HTML_Tag::create( 'h3', 'Event' );
			print_m( $event );
		}
		if( $data->get( 'exception' ) instanceof Exception ){
			$e	= $data->get( 'exception' );
			print UI_HTML_Tag::create( 'h3', 'Exception' );
			print UI_HTML_Tag::create( 'h4', 'Message / Code' );
			print UI_HTML_Tag::create( 'p', $e->getMessage().' ('.$e->getCode().')' );
			print UI_HTML_Tag::create( 'h4', 'File / Line' );
			print UI_HTML_Tag::create( 'p', $e->getFile().' @ '.$e->getLine() );
			print UI_HTML_Tag::create( 'h4', 'Trace' );
			print UI_HTML_Tag::create( 'pre', $e->getTraceAsString() );
		}
		$this->setSubject( 'Event handling failed' );
		$this->setHtml( $buffer->get( TRUE ) );
	}
}
?>
