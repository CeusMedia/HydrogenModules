<?php
class Mail_Form_Customer_Result extends Mail_Form_Abstract{

	public $fill;
	public $form;
	public $mail;

	public function generate( $data = array() ){
		if( !isset( $this->data['form'] ) )
			throw new InvalidArgumentException( 'No form data given' );
		if( !isset( $this->data['fill'] ) )
			throw new InvalidArgumentException( 'No fill data given' );
		if( !isset( $this->data['mail'] ) )
			throw new InvalidArgumentException( 'No mail data given' );
		$this->setForm( $this->data['form'] );
		$this->setFill( $this->data['fill'] );
		$this->setMail( $this->data['mail'] );

		$content	= $this->mail->content;
		$content	= str_replace( "[form_title]", $this->form->title, $content );

		if( $this->mail->format == Model_Form_Mail::FORMAT_HTML ){
			$content	= $this->applyFillData( $content, $this->fill );
			$content	= $this->applyHelpers( $content, $this->fill, $this->form );
			$this->setHtml( $content );
			return (object) array(
				'html'	=> $content,
				'text'	=> NULL,
			);
		}
		$this->setText( $content );
		return (object) array(
			'text'	=> $content,
			'html'	=> NULL,
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

	public function setMail( $mail ){
		$this->mail		= $mail;
		return $this;
	}
}
