<?php
class Mail_Mangopay_Event_Payin extends Mail_Abstract{

	protected function generate( $data = array() ){
		$data	= $this->data ? $this->data : $data;
		$contentHtml	= $this->renderHtml( $data );
		$contentText	= $this->renderText( $data );
		$this->setSubject( 'Event: '. $data['event']->type );
		$this->setHtml( $contentHtml );
		$this->setText( $contentText );
		return (object) array(
			'html'	=> $contentHtml,
			'text'	=> $contentText,
		);
	}

	protected function renderHtml( $data ){
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'h4', 'PayIn' ),
			print_m( $data['payin'], NULL, NULL, TRUE ),
			UI_HTML_Tag::create( 'h4', 'User' ),
			print_m( $data['user'], NULL, NULL, TRUE ),
			UI_HTML_Tag::create( 'h4', 'Model Payin Data' ),
			print_m( $data['data'], NULL, NULL, TRUE ),
		) );
	}

	protected function renderText( $data ){
		$buffer	= new UI_OutputBuffer();
		print( View_Helper_Mail_Text::underscore( $data['event']->type, '=' ) );
		print( PHP_EOL.View_Helper_Mail_Text::underscore( 'Payin' ) );
		print_m( $data['payin'], NULL, NULL, FALSE, 'console' );
		print( PHP_EOL.View_Helper_Mail_Text::underscore( 'User' ) );
		print_m( $data['user'], NULL, NULL, FALSE, 'console' );
		print( PHP_EOL.View_Helper_Mail_Text::underscore( 'Payin Model Data' ) );
		print_m( $data['data'], NULL, NULL, FALSE, 'console' );
		return $buffer->get( TRUE );
	}
}
