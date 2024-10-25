<?php
class Mail_Form_Manager_Error extends Mail_Form_Abstract
{
	/**
	 *	@return		self
	 *	@todo		use block of mail
	 */
	public function generate(): static
	{
		$errorMsg	= $this->data['error'];
		$formData	= $this->data['data'];
		$form		= $this->data['form'];

		$modelMail	= new Model_Form_Mail( $this->env );
		$mail		= $modelMail->getByIndex( 'identifier', 'manager_filled' );
		if( !$mail )
			throw new RuntimeException( 'No form fill manager/receiver mail defined (shortcode: manager_filled)' );

		$content	= str_replace( "[form_title]", $form->title, $mail->content );
//		$content	= $this->applyHelpers( $content, $fill, $form );
		$this->setHtml( $content );
		return $this;
	}
}
