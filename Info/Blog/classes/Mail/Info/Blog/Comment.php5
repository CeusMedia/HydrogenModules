<?php
class Mail_Info_Blog_Comment extends Mail_Abstract{

	protected function generate( $data = array() ){
		$config			= $this->env->getConfig()->getAll( 'module.info_blog.', TRUE );
		$words			= $this->env->getLanguage()->getWords( 'info/blog' );

		$data['comment']->date	= date( 'd.m.Y', $data['comment']->createdAt );
		$data['comment']->time	= date( 'H:i', $data['comment']->createdAt );

		$contentText	= $this->view->loadContentFile( 'mail/info/blog/comment.txt', $data );
		$this->addTextBody( $contentText );

		$contentHtml	= $this->view->loadContentFile( 'mail/info/blog/comment.html', $data );
		$this->page->addBody( $contentHtml );
		$this->addHtmlBody( $this->page->build() );

		$mailSubject	= sprintf( $words['mailSubjects']['comment'], $data['post']->title );
		$this->setSubject( $mailSubject );
		$this->setSender( $config->get( 'mail.sender' ) );
	}
}
?>
