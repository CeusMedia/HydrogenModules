<?php
class Mail_Form_Customer_Result extends Mail_Form_Abstract
{
	/**
	 *	@return		self
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function generate(): static
	{
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

		if( count( $form->attachments ?? [] ) !== 0 ){
			$path	= $this->env->getConfig()->get( 'module.resource_mail.path.attachments' );
			foreach( $form->attachments as $attachment )
				$this->addAttachment( $path.$attachment );
		}
		return $this;
	}
}
