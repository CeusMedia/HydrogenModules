<?php
class Mail_Info_Blog_Comment extends Mail_Abstract
{
	protected function generate(): static
	{
		$config		= $this->env->getConfig()->getAll( 'module.info_blog.', TRUE );
		$words		= $this->env->getLanguage()->getWords( 'info/blog' );
		$data		= $this->data;

		$data['comment']->date	= date( 'd.m.Y', $data['comment']->createdAt );
		$data['comment']->time	= date( 'H:i', $data['comment']->createdAt );

		$mailSubject	= sprintf( $words['mailSubjects']['comment'], $data['post']->title );
		$this->setSubject( $mailSubject );
		$this->setSender( $config->get( 'mail.sender' ) );

		$contentText	= $this->loadContentFile( 'mail/info/blog/comment.txt', $data ) ?? '';
		$this->setText( $contentText );

		$contentHtml	= $this->loadContentFile( 'mail/info/blog/comment.html', $data ) ?? '';
		$this->page->addBody( $contentHtml );
		$this->setHtml( $this->page->build() );

		return $this;
	}
}
