<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Info_Blog extends View
{
	public function index()
	{
	}

	public function post()
	{
	}

	public static function onViewRenderContent( Environment $env, object $context, $module, array & $payload = [] )
	{
		$pattern	= "/^(.*)(\[blog:(.+)\])(.*)$/sU";
		while( preg_match( $pattern, $payload['content'] ) ){
			$id				= trim( preg_replace( $pattern, "\\3", $payload['content'] ) );
			$content		= View_Info_Blog::renderPostAbstractPanelStatic( $env, $id );
			$replacement	= "\\1".$content."\\4";													//  insert content of nested page...
			$payload['content']	= preg_replace( $pattern, $replacement, $payload['content'] );				//  ...into page content
		}
	}

	public static function renderCommentInfoBarStatic( Environment $env, $comment ){
		$facts	= array(
			'Autor: '	=> $comment->username,
			'Datum: '	=> date( 'd.m.Y H:i', $comment->createdAt ),
		);
		$facts		= self::renderFactsStatic( $env, $facts, 'dl-inline' );
		return HtmlTag::create( 'div', $facts, ['class' => 'infobar blog-comment-info'] );
	}

	public function renderComment( $comment ){
		return self::renderCommentStatic( $this->env, $comment );
	}

	public static function renderCommentStatic( Environment $env, $comment ){
		$infobar	= self::renderCommentInfoBarStatic( $env, $comment );
		$content	= HtmlTag::create( 'blockquote', nl2br( trim( $comment->content ) ) );
		$html		= HtmlTag::create( 'div', $infobar.$content, array(
			'class'		=> 'list-comments-item'
		) );
		return $html;
	}

	public static function renderPostAbstractPanelStatic( Environment $env, $modeOrId ){
		$words 	= $env->getLanguage()->getWords( 'info/blog' );
		$model	= new Model_Blog_Post( $env );
		$post	= NULL;
		if( $modeOrId === "random" ){
			$number	= $model->countByIndex( 'status', 1 );
			$index	= rand( 1, $number ) - 1;
			$orders	= ['postId' => 'DESC'];
			$limits	= [1, $index];
			$posts	= $model->getAll( ['status' => 1], $orders, $limits );
			$post	= $posts[0];
			$title	= $words['panelTitles']['typeRandom'];
		}
		else if( in_array( $modeOrId, ["latest", "0"] ) ){
			$post	= $model->getByIndex( 'status', 1, [], [], ['postId' => 'DESC'] );
			$title	= $words['panelTitles']['typeLatest'];
		}
		else if( $modeOrId ){
			$post	= $model->get( $modeOrId );
			$title	= $words['panelTitles']['typeDefault'];
		}
		if( !$post )
			return;
		$content		= self::renderPostAbstractStatic( $env, $post, FALSE );				//  load nested page content
		$heading		= HtmlTag::create( 'h3', $title );
		$panelInner		= HtmlTag::create( 'div', $content, array(
			'class'		=> 'content-panel-inner moduleInfoBlog'
		) );
		return HtmlTag::create( 'div', $heading.$panelInner, array(
			'class'		=> 'content-panel content-panel-info'
		) );
	}

	public static function renderPostAbstractStatic( Environment $env, $post, $showInfoBar = TRUE )
	{
		$title		= HtmlTag::create( 'h4', $post->title );
		$url		= View_Info_Blog::renderPostUrlStatic( $env, $post );
		$title		= HtmlTag::create( 'a', $title, ['href' => $url] );
		$payload	= [
			'content'	=> $post->abstract,
			'type'		=> 'html',
		];
		$view		= new View( $env );
		$words		= $view->getWords( 'index', 'info/blog' );
		$env->getCaptain()->callHook( 'View', 'onRenderContent', $view, $payload );
		$abstract	= $payload['content'];
		$linkView	= HtmlTag::create( 'a', $words->linkMore, array(
			'href'	=> './info/blog/post/'.$post->postId,
		) );
		$clearfloat	= HtmlTag::create( 'div', '', ['class' => 'clearfix'] );
		$linkView	= HtmlTag::create( 'small', $linkView );
		$infobar	= View_Info_Blog::renderPostInfoBarStatic( $env, $post );
		$content	= array(
			$title,
			$abstract.'&nbsp;'.$linkView.$clearfloat,
			$showInfoBar ? $infobar : '',
		);
		return HtmlTag::create( 'div', $content, ['class' => 'blog-post'] );
	}

	public static function renderPostInfoBarStatic( Environment $env, $post )
	{
		if( !isset( $post->author ) ){
			$modelUser		= new Model_User( $env );
			$post->author	= $modelUser->get( $post->authorId );
		}
		$authorName	= $post->author->username;
		if( isset( $post->author->firstname ) && isset( $post->author->surname ) )
			$authorName	= $post->author->firstname.' '.$post->author->surname;
		$facts	= array(
			'Autor: '	=> $authorName,
			'Datum: '	=> date( 'd.m.Y H:i', $post->createdAt ),
			'Gelesen: '	=> $post->nrViews.' mal',
//			'Kommentare: '	=> count( $post->comments ),
		);

		$facts		= self::renderFactsStatic( $env, $facts, 'dl-inline' );
		return HtmlTag::create( 'div', $facts, ['class' => 'infobar blog-post-info hidden-phone'] );
	}

	public static function renderPostUrlStatic( Environment $env, $post )
	{
		$title	= Controller_Info_Blog::getUriPart( $post->title );
		return './info/blog/post/'.$post->postId.'-'.$title;
	}

	protected static function renderFactsStatic( Environment $env, $facts, $listClass = 'dl-horizontal' ){
		$list	= [];
		foreach( $facts as $label => $value ){
			$list[]	= HtmlTag::create( 'dt', $label ).HtmlTag::create( 'dd', $value );
		}
		return HtmlTag::create( 'dl', $list, ['class' => $listClass] );
	}
}
