<?php
class Mail_Test extends Mail_Abstract{

	public function generate( $data = array() ){
		$this->setSubject( 'Test' );
		$contentText	= $this->renderText( $data );
		$contentHtml	= $this->renderHtml( $data );
		$this->setText( $contentText );
		$this->setHtml( $contentHtml );
		return array(
			'text'	=> $contentText,
			'html'	=> $contentHtml,
		);
	}

	public function renderHtml( $data = array() ){
		$content	= '
<h2>E-Mail-Test</h2>
<div>
	<big>This is a text.</big>
	<div class="alert alert-info">The current timestamp is '.time().'.</div>
</div>';
		return $content;
	}

	public function renderText( $data = array() ){
		$content	= '
*E-Mail-Test*

This is just a test.
The current timestamp ist '.time().'

Goodbye';
		return $content;
	}
}
?>
