<?php
class Mail_Forum_Daily extends Mail_Forum_Abstract{

	public function renderBody( $data = array() ){
		$this->setSubject( 'Neue Themen und Beiträge' );
		return '<h1>Mail: Daily</h1>';
	}
}
?>