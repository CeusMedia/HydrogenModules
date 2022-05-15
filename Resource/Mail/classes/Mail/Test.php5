<?php
class Mail_Test extends Mail_Abstract
{
	public function generate(): self
	{
		if( isset( $this->data['verbose'] ) && is_bool( $this->data['verbose'] ) )
			$this->transport->setVerbose( $this->data['verbose'] );

		$data		= new ADT_List_Dictionary( $data );
		$subject	= $data->get( 'subject', 'Test' );
		$text		= $data->get( 'text', $this->renderText() );
		$html		= $data->get( 'html', $this->renderHtml() );

		if( !empty( $data['forceTemplateId'] ) )
			$this->setTemplateId( $data['forceTemplateId'] );

		$this->setSubject( $subject );
		$this->setText( $text );
		$this->setHtml( $html );
		return $this;
	}

	protected function renderHtml(): string
	{
		$content	= '
<h2>E-Mail-Test</h2>
<div>
	<big>This is a text.</big>
	<div class="alert alert-info">The current timestamp is '.time().'.</div>
</div>';
		return $content;
	}

	protected function renderText(): string
	{
		$content	= '
*E-Mail-Test*

This is just a test.
The current timestamp ist '.time().'

Goodbye';
		return $content;
	}
}
