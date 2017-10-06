<?php
class Mail_Mangopay_EventUnverfied extends Mail_Abstract{

	protected function generate( $data = array() ){
		$data		= new ADT_List_Dictionary( $this->data );
		$buffer		= new UI_OutputBuffer();

		$event		= $data->get( 'event' );
		$entity		= $data->get( 'entity' );

		print UI_HTML_Tag::create( 'h2', 'Event verification failed' );
		print UI_HTML_Tag::create( 'h3', 'Event' );
		print_m( $event );
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
			print_m( $entity );
		}
		print UI_HTML_Tag::create( 'h3', 'Info' );
		phpinfo( INFO_VARIABLES );
		$this->setSubject( 'Event verification failed' );
		$this->setHtml( $buffer->get( TRUE ) );
	}
}
?>
