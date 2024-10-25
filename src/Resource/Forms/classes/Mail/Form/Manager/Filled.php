<?php
class Mail_Form_Manager_Filled extends Mail_Form_Abstract
{
	/**
	 *	@return		self
	 *	@todo		use block of mail
	 */
	public function generate(): static
	{
		$form	= $this->data['form'];
		$fill	= $this->data['fill'];

		$modelMail	= new Model_Form_Mail( $this->env );
		$mail		= $modelMail->getByIndex( 'identifier', 'manager_filled' );
		if( !$mail )
			throw new RuntimeException( 'No form fill manager/receiver mail defined (shortcode: manager_filled)' );

		$content	= str_replace( "[form_title]", $form->title, $mail->content );
		$content	= $this->applyHelpers( $content, $fill, $form, TRUE );
		$this->setHtml( $content );
		return $this;
	}
}
