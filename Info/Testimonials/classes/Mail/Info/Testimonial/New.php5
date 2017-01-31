<?php
class Mail_Info_Testimonial_New extends Mail_Abstract{

	protected function generate( $data = array() ){
		$config			= $this->env->getConfig()->getAll( 'module.info_testimonials.', TRUE );
		$words			= $this->env->getLanguage()->getWords( 'info/testimonial' );

//		$data['url']	= $this->env->url.'manage/testimonial/edit/'.$data['entry']->testimonialId;

		$contentText	= $this->view->loadContentFile( 'mail/info/testimonial/new.txt', $data );
		$this->addTextBody( $contentText );

		$contentHtml	= $this->view->loadContentFile( 'mail/info/testimonial/new.html', $data );
		$this->page->addBody( $contentHtml );
		$this->addHtmlBody( $this->page->build() );

		$mailSubject	= sprintf( $words['mailSubjects']['new'], $data['entry']->title );
		$this->setSubject( $mailSubject );
//		$this->setSender( $config->get( 'mail.sender' ) );
	}
}
?>
