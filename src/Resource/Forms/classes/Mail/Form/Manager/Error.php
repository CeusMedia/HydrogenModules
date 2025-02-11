<?php
class Mail_Form_Manager_Error extends Mail_Form_Abstract
{
	/**
	 *	@return		static
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function generate(): static
	{
		$errorMsg	= $this->data['error'];
		$formData	= $this->data['data'];

		/** @var ?Entity_Form $form */
		$form		= $this->data['form'];

		$modelMail	= new Model_Form_Mail( $this->env );

		/** @var ?Entity_Form_Mail $mail */
		$mail		= $modelMail->getByIndex( 'identifier', 'manager_filled' );
		if( NULL === $mail )
			throw new RuntimeException( 'No form fill manager/receiver mail defined (shortcode: manager_filled)' );

		$content	= str_replace( "[form_title]", $form->title, $mail->content );
//		$content	= $this->applyHelpers( $content, $fill, $form );
		$this->setHtml( $content );
		return $this;
	}
}
