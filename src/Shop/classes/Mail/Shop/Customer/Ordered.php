<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

class Mail_Shop_Customer_Ordered extends Mail_Shop_Abstract
{
	/**
	 *	@return		self
	 *	@throws		RangeException
	 *	@throws		InvalidArgumentException
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		$wordsMail	= (object) $this->words['mail-customer-ordered'];
		$this->setSubject( $this->renderSubject( $wordsMail->subject ) );
		$this->setText( $this->renderText() );
		$this->setHtml( $this->renderHtml() );
		return $this;
	}

	/**
	 *	Renders content of HTML Mail.
	 *	@return		string
	 *	@throws		RuntimeException		if collecting data for template or rendering failed
	 */
	protected function renderHtml(): string
	{
		try{
			$templateFile	= 'mail/shop/customer/ordered.html';
			$outputFormat	= View_Helper_Shop_CartPositions::OUTPUT_HTML;
			$templateData	= $this->getContentTemplateData( $outputFormat );
			return $this->loadContentFile( $templateFile, $templateData );
		}
		catch( Throwable $e ){
			$this->env->getLog()->logException( $e );
			throw new RuntimeException( 'Rendering mail template failed', 0, $e );
		}
	}

	/**
	 *	Renders content of plain text mail.
	 *	@return		string
	 *	@throws		RuntimeException		if collecting data for template or rendering failed
	 */
	public function renderText(): string
	{
		try{
			$templateFile	= 'mail/shop/customer/ordered.txt';
			$outputFormat	= View_Helper_Shop_CartPositions::OUTPUT_TEXT;
			$templateData	= $this->getContentTemplateData( $outputFormat );
			return $this->loadContentFile( $templateFile, $templateData );
		}
		catch( Throwable $e ){
			$this->env->getLog()->logException( $e );
			throw new RuntimeException( 'Rendering mail template failed', 0, $e );
		}
	}
}

class Mail_Shop_Order_Customer extends Mail_Shop_Customer_Ordered
{
}
