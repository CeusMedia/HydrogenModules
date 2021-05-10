<?php
class Mail_Test extends Mail_Abstract
{
	public function generate( array $data = array() )
	{
		if( isset( $data['verbose'] ) && is_bool( $data['verbose'] ) )
			$this->transport->setVerbose( $data['verbose'] );

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

	protected function renderHtml( ADT_List_Dictionary $viewData ): string
	{
		$content	= '
<h2>E-Mail-Test</h2>
<div>
	<big>This is a text.</big>
	<div class="alert alert-info">The current timestamp is '.time().'.</div>
</div>';
		return $content;
	}

	protected function renderText( ADT_List_Dictionary $viewData ): string
	{
		$content	= '
*E-Mail-Test*

This is just a test.
The current timestamp ist '.time().'

Goodbye';
		return $content;
	}
}
