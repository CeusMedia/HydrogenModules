<?php
class Mail_Form_Customer_Result extends Mail_Form_Abstract{

	public $fill;
	public $form;
	public $mail;

	public function render(){
		$content	= $this->mail->content;
		$content	= str_replace( "[form_title]", $this->form->title, $content );
		$data		= json_decode( $this->fill->data, TRUE );

		$content	= $this->applyFillData( $content, $this->fill );
		$content	= $this->applyHelpers( $content, $this->fill, $this->form );

		if( $this->mail->format == Model_Mail::FORMAT_HTML )
			return $this->renderPage( $content );
		return $content;
	}

	public function setFill( $fill ){
		$this->fill		= $fill;
		return $this;
	}

	public function setForm( $form ){
		$this->form		= $form;
		return $this;
	}

	public function setMail( $mail ){
		$this->mail		= $mail;
		return $this;
	}
}

