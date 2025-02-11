<?php
class Mail_Form_Customer_Confirm extends Mail_Form_Abstract
{
	/**
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function generate(): static
	{
		$modelMail	= new Model_Form_Mail( $this->env );

		/** @var ?Entity_Form $form */
		$form	= $this->data['form'];

		/** @var ?Entity_Form_Fill $fill */
		$fill	= $this->data['fill'];

		/** @var ?Entity_Form_Mail $mail */
		$mail		= $modelMail->getByIndex( 'identifier', 'customer_confirm' );
		if( NULL === $mail )
			throw new RuntimeException( 'No confirmation mail defined (shortcode: customer_confirm)' );

		$content		= $mail->content;
		$linkConfirm	= $this->env->getConfig()->get( 'app.base.url' ).'manage/form/fill/confirm/'.$fill->fillId;
		$content		= str_replace( "[form_title]", $form->title, $content );
		$content		= str_replace( "[link_confirm]", $linkConfirm, $content );
		if( Model_Form_Mail::FORMAT_HTML === $mail->format ){
			$content	= $this->applyFillData( $content, $fill );
			$content	= $this->applyHelpers( $content, $fill, $form );
			$this->setHtml( $content );
		}
		else
			$this->setText( $content );
		return $this;
	}
}
