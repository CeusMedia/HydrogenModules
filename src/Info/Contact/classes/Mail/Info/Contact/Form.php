<?php
class Mail_Info_Contact_Form extends Mail_Abstract
{
	/**
	 *	@return		static
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		$config		= $this->env->getConfig()->getAll( 'module.info_contact.', TRUE );
		$words		= $this->env->getLanguage()->getWords( 'info/contact' );
		$data		= $this->data;

		$do			= (object) [
			'email'		=> strip_tags( $data['email'] ?? '' ),
			'phone'		=> strip_tags( $data['phone'] ?? '' ),
			'type'		=> strip_tags( $data['type'] ?? '' ),
			'subject'	=> strip_tags( $data['subject'] ?? '' ),
			'person'	=> strip_tags( $data['person'] ?? '' ),
			'company'	=> strip_tags( $data['company'] ?? '' ),
			'street'	=> strip_tags( $data['street'] ?? '' ),
			'city'		=> strip_tags( $data['city'] ?? '' ),
			'postcode'	=> strip_tags( $data['postcode'] ?? '' ),
			'body'		=> strip_tags( $data['body'] ?? '' ),
		];

		if( '' === $do->type )
			$do->type	= current( array_keys( $words['form-types'] ) );

		$wordsMail		= $words['mail'];
		if( array_key_exists( 'mail-type-'.$do->type, $words ) )
			$wordsMail	= array_merge( $wordsMail, $words['mail-type-'.$do->type] );

		$mailSubject	= vsprintf( $wordsMail['subject'], [
			$do->subject,
			$do->person,
			$do->email,
		] );

		$this->setSubject( $mailSubject );
		$this->setSender( $config->get( 'mail.sender' ) );

		$salutations	= array_values( $words['mailSalutations'] );
		$salutation		= $salutations[array_rand($salutations)];
		$valueAddress	= $do->street ? $do->street.', '.$do->postcode.' '.$do->city : '';

		$this->setHtml( $this->loadContentFile( 'mail/info/contact/form.html', [
			'salutation'	=> $salutation,
			'email'			=> htmlentities( $do->email, ENT_QUOTES, 'UTF-8' ),
			'type'			=> $words['form-types'][$do->type],
			'subject'		=> htmlentities( $do->subject, ENT_QUOTES, 'UTF-8' ),
			'person'		=> htmlentities( $do->person, ENT_QUOTES, 'UTF-8' ),
			'company'		=> htmlentities( $do->company, ENT_QUOTES, 'UTF-8' ),
			'address'		=> htmlentities( $valueAddress, ENT_QUOTES, 'UTF-8' ),
			'body'			=> nl2br( htmlentities( $do->body, ENT_QUOTES, 'UTF-8' ) ),
		] ) ?? '' );
		$this->setText( $this->loadContentFile( 'mail/info/contact/form.txt', [
			'salutation'	=> $salutation,
			'email'			=> $do->email,
			'type'			=> $words['form-types'][$do->type],
			'subject'		=> $do->subject,
			'person'		=> $do->person,
			'company'		=> $do->company,
			'address'		=> $valueAddress,
			'body'			=> $do->body,
		] ) ?? '' );
		return $this;
	}
}
