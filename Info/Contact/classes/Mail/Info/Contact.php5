<?php
class Mail_Info_Contact extends Mail_Abstract{

	protected function generate( $data = array() ){
		$config			= $this->env->getConfig()->getAll( 'module.info_contact.', TRUE );
		$words			= $this->env->getLanguage()->getWords( 'info/contact' );
		$data			= (object) $data;

		$mailSubject	= $words['mail']['subject'];
		$mailSubject	= sprintf( $mailSubject, $data->subject, $data->name, $data->email );
		$this->setSubject( $mailSubject );
		$this->setSender( $config->get( 'mail.sender' ) );

		$salutations	= array_values( $words['mailSalutations'] );
		$html			= $this->view->loadContentFile( 'mail/info/contact.html', array(
			'salutation'	=> $salutations[array_rand($salutations)],
			'email'			=> htmlentities( strip_tags( $data->email ), ENT_QUOTES, 'UTF-8' ),
			'subject'		=> htmlentities( strip_tags( $data->subject ), ENT_QUOTES, 'UTF-8' ),
			'name'			=> htmlentities( strip_tags( $data->name ), ENT_QUOTES, 'UTF-8' ),
			'message'		=> nl2br( htmlentities( strip_tags( $data->message ), ENT_QUOTES, 'UTF-8' ) ),
			'newsletter'	=> !empty( $data->newsletter ) ? 'ja' : 'nein',
		) );
		$this->addHtmlBody( $html );

		return (object) array(
			'plain'	=> NULL,
			'html'	=> $html,
		);
	}
}
?>
