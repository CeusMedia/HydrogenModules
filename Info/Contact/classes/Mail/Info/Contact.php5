<?php
class Mail_Info_Contact extends Mail_Abstract{
	protected function generate( $data = array() ){

		$words	= $this->env->getLanguage()->getWords( 'info/contact' );
		$salutations		= array_values( $words['mailSalutations'] );
		$data['salutation']	= $salutations[array_rand($salutations)];

		$content	= $this->view->loadContentFile( 'mail/info/contact.html', $data );
		$this->page->addBody( $content );



		print( $this->page->build() );
die;
		print_m( $data );
		die;
	}
}
?>
