<?php
class Mail_Forum_Daily extends Mail_Forum_Abstract{

	public function renderBody( $data = array() ){
//		extract( $data );

		$this->setSubject( 'Moderation notwendig' );

		$heading		= $this->env->title;

		if( $data['posts'] ){
			$list	= array();
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
			$rows	= array();
			foreach( $list as $thread ){
				$posts		= UI_HTML_Tag::create( 'b', count( $thread->posts ).' '.( count( $thread->posts ) === 1 ? 'Beitrag' : 'Beiträge' ) );
				$linkTopic	= UI_HTML_Tag::create( 'a', $thread->topic->title, array(
					'href'		=> $this->env->url.'info/forum/topic/'.$thread->topic->topicId,
					'class'		=> 'link-topic'
				) );
				$linkThread	= UI_HTML_Tag::create( 'a', $thread->thread->title, array(
					'href'		=> $this->env->url.'info/forum/thread/'.$thread->thread->threadId,
					'class'		=> 'link-thread'
				) );
				$line		= '%1$s > %2$s: %3$s';
				$line		= '%3$s in Kategorie %1$s in Thema %2$s';
				$line		= sprintf( $line, $linkTopic, $linkThread, $posts );
				$rows[]		= UI_HTML_Tag::create( 'li', $line );
			}
			$list	= UI_HTML_Tag::create( 'ul', $rows, array( 'class' => '' ) );
		}
		$body	= '
<div id="layout-mail">
	<div class="layout-header">
		<div class="container">
			<h2><a href="'.$this->env->url.'info/forum">Forum</a> @ <a href="'.$this->env->url.'">'.$heading.'</a></h2>
		</div>	
	</div>
	<div class="layout-content">
		<div class="container">
			<div class="intro">
				<div class="salutation">Hallo '.$data['user']->username.'!</div>
				Es gibt etwas zu moderieren:
			</div>
			'.$list.'
		</div>
	</div>
</div>';
		$this->addPrimerStyle( 'layout.css' );
		$this->addThemeStyle( 'bootstrap.css' );
		$this->addThemeStyle( 'layout.css' );
		$this->addThemeStyle( 'module.info.forum.css' );
		$this->page->addBody( $body );
		$this->page->setBaseHref( $this->env->url );
		$class	= 'moduleInfoForum jobInfoForum info-forum-mail info-forum-mail-answer';
		return $this->page->build( array( 'class' => $class ) );
	}
}
?>
