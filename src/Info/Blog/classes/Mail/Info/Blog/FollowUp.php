<?php
class Mail_Info_Blog_FollowUp extends Mail_Abstract
{
	protected function generate(): self
	{
		$config		= $this->env->getConfig()->getAll( 'module.info_blog.', TRUE );
		$words		= $this->env->getLanguage()->getWords( 'info/blog' );
		$data		= $this->data;

		$data['myComment']->date	= date( 'd.m.Y', $data['myComment']->createdAt );
		$data['myComment']->time	= date( 'H:i', $data['myComment']->createdAt );
		$data['comment']->date		= date( 'd.m.Y', $data['comment']->createdAt );
		$data['comment']->time		= date( 'H:i', $data['comment']->createdAt );

		$mailSubject	= $words['mailSubjects']['comment'];
		$mailSubject	= sprintf( $mailSubject, $data['post']->title );
		$this->setSubject( $mailSubject );
		$this->setSender( $config->get( 'mail.sender' ) );

		$contentText	= $this->view->loadContentFile( 'mail/info/blog/followup.txt', $data );
		$this->setText( $contentText );

		$contentHtml	= $this->view->loadContentFile( 'mail/info/blog/followup.html', $data );
		$this->page->addBody( $contentHtml );
		$this->setHtml( $this->page->build() );

		return $this;
	}
}
