<?php
class Mail_Info_Blog_FollowUp extends Mail_Abstract{

	protected function generate( $data = array() ){
		$config			= $this->env->getConfig()->getAll( 'module.info_blog.', TRUE );
		$words			= $this->env->getLanguage()->getWords( 'info/blog' );

		$data['myComment']->date	= date( 'd.m.Y', $data['myComment']->createdAt );
		$data['myComment']->time	= date( 'H:i', $data['myComment']->createdAt );
		$data['comment']->date		= date( 'd.m.Y', $data['comment']->createdAt );
		$data['comment']->time		= date( 'H:i', $data['comment']->createdAt );

		$contentText	= $this->view->loadContentFile( 'mail/info/blog/followup.txt', $data );
		$this->addTextBody( $contentText );

		$contentHtml	= $this->view->loadContentFile( 'mail/info/blog/followup.html', $data );
		$this->page->addBody( $contentHtml );
		$this->addHtmlBody( $this->page->build() );

		$mailSubject	= $words['mailSubjects']['comment'];
		$mailSubject	= sprintf( $mailSubject, $data['post']->title );
		$this->setSubject( $mailSubject );
		$this->setSender( $config->get( 'mail.sender' ) );
	}
}
?>
