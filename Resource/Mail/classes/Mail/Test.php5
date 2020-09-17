<?php
class Mail_Test extends Mail_Abstract
{
	public function generate( $data = array() )
	{
		$data		= new ADT_List_Dictionary( $data );
		$subject	= $data->get( 'subject', 'Test' );
		$text		= $data->get( 'text', $this->renderText( $data ) );
		$html		= $data->get( 'html', $this->renderHtml( $data ) );

		if( !empty( $data['forceTemplateId'] ) )
			$this->setTemplateId( $data['forceTemplateId'] );

		$this->setSubject( $subject );
		$this->setText( $text );
		$this->setHtml( $html );
		return $this;
	}

	public function renderHtml( $data = array() )
	{
		$content	= '
<h2>E-Mail-Test</h2>
<div>
	<big>This is a text.</big>
	<div class="alert alert-info">The current timestamp is '.time().'.</div>
</div>';
		return $content;
	}

	public function renderText( $data = array() )
	{
		$content	= '
*E-Mail-Test*

This is just a test.
The current timestamp ist '.time().'

Goodbye';
		return $content;
	}
}
