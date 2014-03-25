<?php
class Mail_Forum_Answer extends Mail_Forum_Abstract{

	public function renderBody( $data = array() ){
		$this->setSubject( 'Reaktion in deinem Thema "Test"' );
		return '<h1>Mail: Answer</h1>';
	}
}
?>