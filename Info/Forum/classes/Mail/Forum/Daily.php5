<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Forum_Daily extends Mail_Forum_Abstract
{
	protected function renderHtmlBody(): string
	{
//		extract( $data );
		$data	= $this->data;
		$this->setSubject( 'Moderation im Forum notwendig' );

		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$heading	= $this->env->title;
		$heading	= $wordsMain['main']['title'];

		if( $data['posts'] ){
			$list	= [];
			foreach( $data['posts'] as $post ){
				if( !isset( $list[$post->threadId] ) ){
					$thread		= $this->modelThread->get( $post->threadId );
					$topic		= $this->modelTopic->get( $thread->topicId );
					$list[$post->threadId]	= (object) array(
						'topic'		=> $topic,
						'thread'	=> $thread,
						'posts'		=> array()
					);
				}
				$list[$post->threadId]->posts[$post->postId]	= $post;
			}
			$rows	= [];
			foreach( $list as $thread ){
				$posts		= HtmlTag::create( 'b', count( $thread->posts ).' '.( count( $thread->posts ) === 1 ? 'Beitrag' : 'Beiträge' ) );
				$linkTopic	= HtmlTag::create( 'a', $thread->topic->title, array(
					'href'		=> $this->env->url.'info/forum/topic/'.$thread->topic->topicId,
					'class'		=> 'link-topic'
				) );
				$linkThread	= HtmlTag::create( 'a', $thread->thread->title, array(
					'href'		=> $this->env->url.'info/forum/thread/'.$thread->thread->threadId,
					'class'		=> 'link-thread'
				) );
				$line		= '%1$s > %2$s: %3$s';
				$line		= '%3$s in Kategorie %1$s in Thema %2$s';
				$line		= sprintf( $line, $linkTopic, $linkThread, $posts );
				$rows[]		= HtmlTag::create( 'li', $line );
			}
			$list	= HtmlTag::create( 'ul', $rows, array( 'class' => '' ) );
		}
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
