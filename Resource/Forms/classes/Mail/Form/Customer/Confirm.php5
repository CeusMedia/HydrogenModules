<?php
class Mail_Form_Customer_Confirm extends Mail_Form_Abstract{

	public function generate( $data = array() ){
		$form	= $this->data['form'];
		$fill	= $this->data['fill'];

		$modelMail	= new Model_Form_Mail( $this->env );
		$mail		= $modelMail->getByIndex( 'identifier', 'customer_confirm' );
		if( !$mail )
			throw new RuntimeException( 'No confirmation mail defined (shortcode: customer_confirm)' );

		$content		= $mail->content;
		$linkConfirm	= $this->env->getConfig()->get( 'app.base.url' ).'manage/form/fill/confirm/'.$fill->fillId;
		$content		= str_replace( "[form_title]", $form->title, $content );
		$content		= str_replace( "[link_confirm]", $linkConfirm, $content );
		if( $mail->format == Model_Form_Mail::FORMAT_HTML ){
			$content	= $this->applyFillData( $content, $fill );
			$content	= $this->applyHelpers( $content, $fill, $form );
			$this->setHtml( $content );
		}
		$this->setText( $content );
	}
}
