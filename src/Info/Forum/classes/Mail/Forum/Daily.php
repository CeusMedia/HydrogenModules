<?php

use CeusMedia\Common\Exception\Data\Missing as DataMissingException;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Forum_Daily extends Mail_Forum_Abstract
{
	/**
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function renderHtmlBody(): string
	{
//		extract( $data );
		$data	= $this->data;

		/** @var array<object> $posts */
		$posts	= $this->data['posts'];

		if( [] === $posts )
			throw DataMissingException::create( 'No post given' );

		$this->setSubject( 'Moderation im Forum notwendig' );

//		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$heading	= $this->env->title;
//		$heading	= $wordsMain['main']['title'];

		$postsByThread	= [];
		foreach( $data['posts'] as $post ){
			if( !isset( $postsByThread[$post->threadId] ) ){
				$thread		= $this->modelThread->get( $post->threadId );
				$topic		= $this->modelTopic->get( $thread->topicId );
				$postsByThread[$post->threadId]	= (object) [
					'topic'		=> $topic,
					'thread'	=> $thread,
					'posts'		=> []
				];
			}
			$postsByThread[$post->threadId]->posts[$post->postId]	= $post;
		}


		$rows	= [];
		foreach( $postsByThread as $thread ){
			$posts		= HtmlTag::create( 'b', count( $thread->posts ).' '.( count( $thread->posts ) === 1 ? 'Beitrag' : 'Beiträge' ) );
			$linkTopic	= HtmlTag::create( 'a', $thread->topic->title, [
				'href'		=> $this->env->url.'info/forum/topic/'.$thread->topic->topicId,
				'class'		=> 'link-topic'
			] );
			$linkThread	= HtmlTag::create( 'a', $thread->thread->title, [
				'href'		=> $this->env->url.'info/forum/thread/'.$thread->thread->threadId,
				'class'		=> 'link-thread'
			] );
//			$line		= '%1$s > %2$s: %3$s';
			$line		= '%3$s in Kategorie %1$s in Thema %2$s';
			$line		= sprintf( $line, $linkTopic, $linkThread, $posts );
			$rows[]		= HtmlTag::create( 'li', $line );
		}
		$list	= HtmlTag::create( 'ul', $rows, ['class' => ''] );

		$body	= '
<div class="moduleInfoForum jobInfoForum info-forum-mail info-forum-mail-answer">
	<h2>Forum</h2>
	<div class="intro">
		<div class="salutation">Hallo '.$data['user']->username.'!</div>
		Es gibt etwas zu moderieren:
	</div>
	'.$list.'
</div>';

		$this->addThemeStyle( 'module.info.forum.css' );
		return $body;
	}
}
