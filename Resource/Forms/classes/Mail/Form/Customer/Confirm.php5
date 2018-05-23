<?php
class Mail_Form_Customer_Confirm extends Mail_Form_Abstract{

	public $fill;
	public $form;
	protected $modelMail;

	public function generate( $data = array() ){
		if( !isset( $this->data['form'] ) )
			throw new InvalidArgumentException( 'No form data given' );
		if( !isset( $this->data['fill'] ) )
			throw new InvalidArgumentException( 'No fill data given' );
		$this->setForm( $this->data['form'] );
		$this->setFill( $this->data['fill'] );

		$modelMail	= new Model_Form_Mail( $this->env );
		$mail		= $modelMail->getByIndex( 'identifier', 'customer_confirm' );
		if( !$mail )
			throw new RuntimeException( 'No confirmation mail defined (shortcode: customer_confirm)' );

		$content		= $mail->content;
		$linkConfirm	= $this->env->getConfig()->get( 'app.base.url' ).'manage/form/fill/confirm/'.$this->fill->fillId;
		$content		= str_replace( "[form_title]", $this->form->title, $content );
		$content		= str_replace( "[link_confirm]", $linkConfirm, $content );
		if( $mail->format == Model_Form_Mail::FORMAT_HTML ){
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
			'html'	=> NULL,
			'text'	=> $content,
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
