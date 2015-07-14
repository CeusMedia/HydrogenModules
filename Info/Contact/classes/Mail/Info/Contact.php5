<?php
class Mail_Info_Contact extends Mail_Abstract{
	protected function generate( $data = array() ){
		$config			= $this->env->getConfig()->getAll( 'module.info_contact.', TRUE );
		$words			= $this->env->getLanguage()->getWords( 'info/contact' );
		$salutations	= array_values( $words['mailSalutations'] );

		$data['salutation']	= $salutations[array_rand($salutations)];
		$data['message']	= nl2br( $data['message'] );
		$this->page->addBody( $this->view->loadContentFile( 'mail/info/contact.html', $data ) );
		$this->addHtmlBody( $this->page->build() );

		$data			= (object) $data;
		$mailSubject	= $words['mail']['subject'];
		$mailSubject	= sprintf( $mailSubject, $data->subject, $data->name, $data->email );
		$this->setSubject( $mailSubject );
		$this->setSender( $config->get( 'mail.sender' ) );
//		$this->sendToAddress( $config->get( 'mail.receiver' ) );
	}
}
?>
