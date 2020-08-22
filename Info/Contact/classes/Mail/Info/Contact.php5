<?php
class Mail_Info_Contact extends Mail_Abstract
{
	protected function generate( $data = array() )
	{
		$config		= $this->env->getConfig()->getAll( 'module.info_contact.', TRUE );
		$words		= $this->env->getLanguage()->getWords( 'info/contact' );
		$do			= (object) $data;
		$do			= (object) array(
			'email'			=> strip_tags( @$data['email'] ),
			'subject'		=> strip_tags( @$data['subject'] ),
			'fullname'		=> strip_tags( @$data['fullname'] ),
			'message'		=> strip_tags( @$data['message'] ),
			'newsletter'	=> strip_tags( @$data['newsletter'] ),
		);

		$mailSubject	= $words['mail']['subject'];
		$mailSubject	= sprintf( $mailSubject, $do->subject, $do->fullname, $do->email );
		$this->setSubject( $mailSubject );
		$this->setSender( $config->get( 'mail.sender' ) );

		$salutations		= array_values( $words['mailSalutations'] );
		$salutation			= $salutations[array_rand($salutations)];
		$valueNewsletter	= $words['newsletter-answers'][(int) !empty( $do->newsletter )];

		$this->setHtml( $this->view->loadContentFile( 'mail/info/contact.html', array(
			'salutation'	=> $salutation,
			'email'			=> htmlentities( $do->email, ENT_QUOTES, 'UTF-8' ),
			'subject'		=> htmlentities( $do->subject, ENT_QUOTES, 'UTF-8' ),
			'fullname'		=> htmlentities( $do->fullname, ENT_QUOTES, 'UTF-8' ),
			'message'		=> nl2br( htmlentities( $do->message, ENT_QUOTES, 'UTF-8' ) ),
			'newsletter'	=> $valueNewsletter,
		) ) );
		$this->setText( $this->view->loadContentFile( 'mail/info/contact.txt', array(
			'salutation'	=> $salutation,
			'email'			=> $do->email,
			'subject'		=> $do->subject,
			'fullname'		=> $do->fullname,
			'message'		=> $do->message,
			'newsletter'	=> $valueNewsletter,
		) ) );
		return $this;
	}
}
