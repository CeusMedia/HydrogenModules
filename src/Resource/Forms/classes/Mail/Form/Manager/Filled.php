<?php
class Mail_Form_Manager_Filled extends Mail_Form_Abstract
{
	/**
	 *	@return		static
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function generate(): static
	{
		/** @var ?Entity_Form $form */
		$form	= $this->data['form'];

		/** @var ?Entity_Form_Fill $fill */
		$fill	= $this->data['fill'];

		$modelMail	= new Model_Form_Mail( $this->env );

		/** @var ?Entity_Form_Mail $mail */
		$mail		= $modelMail->getByIndex( 'identifier', 'manager_filled' );
		if( NULL === $mail )
			throw new RuntimeException( 'No form fill manager/receiver mail defined (shortcode: manager_filled)' );

		$content	= str_replace( "[form_title]", $form->title, $mail->content );
		$content	= $this->applyHelpers( $content, $fill, $form, TRUE );
		$this->setHtml( $content );
		return $this;
	}
}
