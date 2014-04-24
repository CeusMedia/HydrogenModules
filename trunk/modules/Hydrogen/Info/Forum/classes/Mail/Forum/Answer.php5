<?php
class Mail_Forum_Answer extends Mail_Forum_Abstract{

	public function renderBody( $data = array() ){
		extract( $data );

		$this->setSubject( 'Antwort im Forum zum Thema: '.$thread->title );

		$heading	= $this->env->title;

		$words		= $this->env->getLanguage()->getWords( 'main' );
		$heading	= $words['main']['title'];

		$content		= nl2br( $post->content, TRUE );
		if( $post->type == 1 ){
			$parts		= explode( "\n", $post->content );
			$title		= $parts[1] ? Alg_Text_Trimmer::trim( $parts[1], 100 ) : '';
			$caption	= $title ? UI_HTML_Tag::create( 'figcaption', htmlentities( $parts[1], ENT_QUOTES, 'UTF-8') ) : '';
			$image		= UI_HTML_Tag::create( 'img', NULL, array(
				'src'	=> 'contents/forum/'.$parts[0],
				'title'	=> htmlentities( $title, ENT_QUOTES, 'UTF-8')
			) );
			$content	= UI_HTML_Tag::create( 'figure', $image.$caption );
		}
		else{
			$matches	= array();
			preg_match_all( '@https?://[\w.:/?&=-_+\@|]+@', $content, $matches );
			foreach( $matches as $match ){
				$label		= preg_replace( '@https?://@', '', $match[0] );
				$content	= str_replace( $match[0], '<a href="'.$match[0].'">'.$label.'</a>', $content );
			}
			if( $this->env->getModules()->has( 'UI_Markdown' ) )
				if( $options->get( '...markdown...' ) )
					$content	= View_Helper_Markdown::transformStatic( $this->env, $content );
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
				<div class="salutation">Hallo '.$user->username.'!</div>
				Auf deinen Beitrag im Forum gibt es eine Reaktion.
			</div>
			<h3>Thema: <a href="'.$this->env->url.'info/forum/thread/'.$thread->threadId.'">'.$thread->title.'</a></h3>
<!--			<div class="post-header">
				Neuer Beitrag von '.$author->username.' vom '.date( 'd.m.Y', $post->createdAt ).' um '.date( 'd.m.Y', $post->createdAt ).':
			</div>-->
			<div class="post-content1">
				<blockquote>
					<p>'.$content.'</p>
					<small>von '.$author->username.' am '.date( 'd.m.Y', $post->createdAt ).' um '.date( 'H:i', $post->createdAt ).'</small>
				<blockquote>
			</div>
		</div>
	</div>
</div>
<style>
.layout-header a {
	color: black;
	}
.layout-content {
	border-top: 5px solid #FF5500;
	}
.intro {
	margin-top: 1em;
	}
.intro .salutation {
	font-weight: bold;
	font-size: 1.15em;
	line-height: 1.5em;
	}
.post-header {
	margin-top: 1em;
	margin-left: 1em;
	font-weight: bold;
	}
.post-content {
	background-color: #F7F7F7;
	border: 1px solid #E7E7E7;
	border-radius: 0.5em;
	padding: 1em 2em;
	}
</style>
';

		$this->addPrimerStyle( 'layout.css' );
		$this->addThemeStyle( 'bootstrap.css' );
		$this->addThemeStyle( 'layout.css' );
		$this->page->addBody( $body );
		$this->page->setBaseHref( $this->env->url );
		$class	= 'moduleInfoForum jobInfoForum job-info-forum-mail-answer';
		echo $this->page->build( array( 'class' => $class ) );
		die;
	}
}
?>