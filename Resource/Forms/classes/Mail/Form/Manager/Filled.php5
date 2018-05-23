<?php
class Mail_Form_Manager_Filled extends Mail_Form_Abstract{

	public $fill;
	public $form;

	/* @todo use block of mail */
	public function generate( $data = array() ){
		if( !isset( $this->data['form'] ) )
			throw new InvalidArgumentException( 'No form data given' );
		if( !isset( $this->data['fill'] ) )
			throw new InvalidArgumentException( 'No fill data given' );
		$this->setForm( $this->data['form'] );
		$this->setFill( $this->data['fill'] );

		$modelMail	= new Model_Form_Mail( $this->env );
		$mail		= $modelMail->getByIndex( 'identifier', 'manager_filled' );
		if( !$mail )
			throw new RuntimeException( 'No form fill manager/receiver mail defined (shortcode: manager_filled)' );

		$helperPerson	= new View_Helper_Form_Fill_Person( $this->env );
		$helperPerson->setFill( $this->fill );
		$helperPerson->setForm( $this->form );

		$helperData		= new View_Helper_Form_Fill_Data( $this->env );
		$helperData->setFill( $this->fill );
		$helperData->setForm( $this->form );

		$content	= $mail->content;
		$content	= str_replace( "[form_title]", $this->form->title, $content );
		$content	= $this->applyHelpers( $content, $this->fill, $this->form );
		$this->setHtml( $content );

		return (object) array(
			'html'	=> $content,
			'text'	=> NULL,
		);
	}

	public function setFill( $fill ){
		$this->fill		= $fill;
		return $this;
	}

	public function setForm( $form ){
		$this->form		= $form;
		return $this;
	}
}
