<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

class Mail_Shop_Manager_Ordered extends Mail_Shop_Abstract
{
	/**
	 *	@return		static
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		$wordsMail	= (object) $this->words['mail-manager-ordered'];
		$this->setSubject( $this->renderSubject( $wordsMail->subject ) );
//		$this->setText( $this->renderText() );
		$this->setHtml( $this->renderHtml() );
		return $this;
	}

	/**
	 *	Renders content of HTML Mail.
	 *	@return		string
	 *	@throws		RuntimeException		if collecting data for template or rendering failed
	 */
	public function renderHtml(): string
	{
		try{
			$templateFile	= 'mail/shop/manager/ordered.html';
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
	 *	@todo		implement
	 */
	protected function renderText(): string
	{
		return '';
	}
}

class Mail_Shop_Order_Manager extends Mail_Shop_Manager_Ordered
{
}
