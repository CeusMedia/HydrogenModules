<?php
class Mail_Info_Newsletter_Register extends Mail_Abstract
{
	public function renderBody( array $data )
	{
		$words				= $this->getWords( 'info/newsletter' );
		$data['salutation']	= $words['salutations'][$data['reader']->gender];
		$data['key']		= substr( md5( 'InfoNewsletterSalt:'.$data['readerId'] ), 10, 10 );
		$data['baseUrl']	= $this->env->url;

		return $this->view->loadContentFile( 'mail/info/newsletter/register.txt', $data );
	}

	protected function generate( $data = array() )
	{
		$words		= (object) $this->getWords( 'info/newsletter', 'register' );
		$prefix	= $this->env->getConfig()->get( 'module.resource_mail.subject.prefix' );
		$subject	= ( $prefix ? $prefix.' ' : '' ) . $words->mailSubject;
		$this->mail->setSubject( $subject );

		$text		= $this->renderBody( $data );
		$this->setText( $text );
		return $text;
	}
}
