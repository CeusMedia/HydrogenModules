<?php
class Mail_Info_Contact_Form extends Mail_Abstract{

	protected function generate( $data = array() ){
		$config			= $this->env->getConfig()->getAll( 'module.info_contact.', TRUE );
		$words			= $this->env->getLanguage()->getWords( 'info/contact' );

		$data			= (object) array(
			'email'		=> htmlentities( strip_tags( @$data['email'] ), ENT_QUOTES, 'UTF-8' ),
			'phone'		=> htmlentities( strip_tags( @$data['phone'] ), ENT_QUOTES, 'UTF-8' ),
			'type'		=> (int) strip_tags( @$data['type'] ),
			'subject'	=> strip_tags( @$data['subject'] ),
			'person'	=> strip_tags( @$data['person'] ),
			'company'	=> strip_tags( @$data['company'] ),
			'street'	=> strip_tags( @$data['street'] ),
			'city'		=> strip_tags( @$data['city'] ),
			'postcode'	=> strip_tags( @$data['postcode'] ),
			'type'		=> strip_tags( @$data['type'] ),
			'body'		=> strip_tags( @$data['body'] ),
		);

		$type			= current( $words['form-types'] );
		if( !empty( $data->type ) ){

		}
		$type	= $words['form-types'][$data->type];

		$wordsMail		= $words['mail'];
		if( array_key_exists( 'mail-type-'.$data->type, $words ) )
			$wordsMail	= array_merge( $wordsMail, $words['mail-type-'.$data->type] );

		$mailSubject	= vsprintf( $wordsMail['subject'], array(
			$data->subject,
			$data->person,
			$data->email,
		) );

		$this->setSubject( $mailSubject );
		$this->setSender( $config->get( 'mail.sender' ) );

		$salutations	= array_values( $words['mailSalutations'] );
		$html	= $this->view->loadContentFile( 'mail/info/contact/form.html', array(
			'salutation'	=> $salutations[array_rand($salutations)],
			'email'			=> htmlentities( $data->email, ENT_QUOTES, 'UTF-8' ),
			'type'			=> $words['form-types'][$data->type],
			'subject'		=> htmlentities( $data->subject, ENT_QUOTES, 'UTF-8' ),
			'person'		=> htmlentities( $data->person, ENT_QUOTES, 'UTF-8' ),
			'company'		=> htmlentities( $data->company, ENT_QUOTES, 'UTF-8' ),
			'address'		=> htmlentities( ( $data->street ? $data->street.', '.$data->postcode.' '.$data->city : '' ), ENT_QUOTES, 'UTF-8' ),
			'body'			=> nl2br( htmlentities( $data->body, ENT_QUOTES, 'UTF-8' ) ),
		) );
		$this->addHtmlBody( $html );

		return (object) array(
			'plain'	=> NULL,
			'html'	=> $html,
		);
	}
}
?>
