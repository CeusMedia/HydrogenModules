<?php /** @noinspection XmlDeprecatedElement */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Exception\IO as IoException;

class Mail_Test extends Mail_Abstract
{
	/**
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		IoException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function generate(): self
	{
		if( isset( $this->data['verbose'] ) && is_bool( $this->data['verbose'] ) )
			$this->transport->setVerbose( $this->data['verbose'] );

		$data		= new Dictionary( $this->data );
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
		/** @noinspection HtmlDeprecatedTag */
		return '
<h2>E-Mail-Test</h2>
<div>
	<big>This is a text.</big>
	<div class="alert alert-info">The current timestamp is '.time().'.</div>
</div>';
	}

	protected function renderText(): string
	{
		return '
*E-Mail-Test*

This is just a test.
The current timestamp ist '.time().'

Goodbye';
	}
}
