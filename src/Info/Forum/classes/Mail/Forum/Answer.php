<?php
use CeusMedia\Common\Alg\Text\Trimmer as TextTrimmer;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Forum_Answer extends Mail_Forum_Abstract
{
	protected function renderHtmlBody(): string
	{
		extract( $this->data );
		$this->setSubject( 'Antwort im Forum zum Thema: '.$thread->title );

		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$heading	= $this->env->title;
		$heading	= $wordsMain['main']['title'];

		$content		= nl2br( $post->content, TRUE );
		if( $post->type == 1 ){
			$parts		= explode( "\n", $post->content );
			$title		= $parts[1] ? TextTrimmer::trim( $parts[1], 100 ) : '';
			$caption	= $title ? HtmlTag::create( 'figcaption', htmlentities( $parts[1], ENT_QUOTES, 'UTF-8') ) : '';
			$image		= HtmlTag::create( 'img', NULL, array(
				'src'	=> 'contents/forum/'.$parts[0],
				'title'	=> htmlentities( $title, ENT_QUOTES, 'UTF-8')
			) );
			$content	= HtmlTag::create( 'figure', $image.$caption );
		}
		else{
			$matches	= [];
			preg_match_all( '@https?://[\w.:/?&=-_+\@|]+@', $content, $matches );
			foreach( $matches as $match ){
				if( array_key_exists( 0, $match ) ){
					$label		= preg_replace( '@https?://@', '', $match[0] );
					$content	= str_replace( $match[0], '<a href="'.$match[0].'">'.$label.'</a>', $content );
				}
			}
			if( $this->env->getModules()->has( 'UI_Markdown' ) )
				if( $options->get( '...markdown...' ) )
					$content	= View_Helper_Markdown::transformStatic( $this->env, $content );
		}

		$body	= '
<div class="moduleInfoForum jobInfoForum info-forum-mail info-forum-mail-answer">
	<h2>Forum</h2>
	<div class="intro">
		<div class="salutation">Hallo '.$user->username.'!</div>
		Auf deinen Beitrag im Forum gibt es eine Reaktion.
	</div>
	<h3>Thema: <a href="'.$this->env->url.'info/forum/thread/'.$thread->threadId.'">'.$thread->title.'</a></h3>
<!--	<div class="post-header">
		Neuer Beitrag von '.$author->username.' vom '.date( 'd.m.Y', $post->createdAt ).' um '.date( 'd.m.Y', $post->createdAt ).':
	</div>-->
	<div class="post-content1">
		<blockquote>
			<p>'.$content.'</p>
			<small>von '.$author->username.' am '.date( 'd.m.Y', $post->createdAt ).' um '.date( 'H:i', $post->createdAt ).'</small>
		<blockquote>
	</div>
</div>';

		$this->addThemeStyle( 'module.info.forum.css' );
		return $body;
	}
}
