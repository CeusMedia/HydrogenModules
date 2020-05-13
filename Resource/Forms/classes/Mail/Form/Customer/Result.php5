<?php
class Mail_Form_Customer_Result extends Mail_Form_Abstract{

	public function generate( $data = array() ){
		$form	= $this->data['form'];
		$fill	= $this->data['fill'];
		$mail	= $this->data['mail'];

		$content	= str_replace( "[form_title]", $form->title, $mail->content );

		if( $mail->format == Model_Form_Mail::FORMAT_HTML ){
			$content	= $this->applyFillData( $content, $fill );
			$content	= $this->applyHelpers( $content, $fill, $form );
			$this->setHtml( $content );
		}
		else
			$this->setText( $content );
	}
}
