<?php
class Mail_Form_Customer_Confirm extends Mail_Form_Abstract{

	public $fill;
	public $form;
	protected $modelMail;

	public function render(){
		$modelMail	= new Model_Form_Mail( $this->env );
		$mail		= $modelMail->getByIndex( 'identifier', 'customer_confirm' );
		if( !$mail )
			throw new RuntimeException( 'No confirmation mail defined' );

		$content	= $mail->content;
		$link		= $this->app->getConfig()->get( 'app.url' ).'?action=fill_confirm&id='.$this->fill->fillId;

		$content	= str_replace( "[form_title]", $this->form->title, $content );
		$content	= str_replace( "[link_confirm]", $link, $content );

		$content	= $this->applyFillData( $content, $this->fill );
		$content	= $this->applyHelpers( $content, $this->fill, $this->form );

		if( $mail->format == Model_Mail::FORMAT_HTML )
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
}

