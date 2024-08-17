<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Captain as CaptainResource;
use CeusMedia\HydrogenFramework\View;

class View_Info_Blog extends View
{
	public static function renderCommentInfoBarStatic( Environment $env, $comment ): string
	{
		$facts	= [
			'Autor: '	=> $comment->username,
			'Datum: '	=> date( 'd.m.Y H:i', $comment->createdAt ),
		];
		$facts		= self::renderFactsStatic( $env, $facts, 'dl-inline' );
		return HtmlTag::create( 'div', $facts, ['class' => 'infobar blog-comment-info'] );
	}

	public static function renderCommentStatic( Environment $env, object $comment ): string
	{
		return HtmlTag::create( 'div', [
			self::renderCommentInfoBarStatic( $env, $comment ),
			HtmlTag::create( 'blockquote', nl2br( trim( $comment->content ) ) ),
		], ['class' => 'list-comments-item'] );
	}

	/**
	 *	@param		Environment $env
	 *	@param		$modeOrId
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public static function renderPostAbstractPanelStatic( Environment $env, $modeOrId ): string
	{
		$words 	= $env->getLanguage()->getWords( 'info/blog' );
		$model	= new Model_Blog_Post( $env );
		$post	= NULL;
		if( $modeOrId === "random" ){
			$number	= $model->countByIndex( 'status', 1 );
			$index	= random_int( 1, $number ) - 1;
			$orders	= ['postId' => 'DESC'];
			$limits	= [1, $index];
			$posts	= $model->getAll( ['status' => 1], $orders, $limits );
			$post	= $posts[0];
			$title	= $words['panelTitles']['typeRandom'];
		}
		else if( in_array( $modeOrId, ['latest', '0'] ) ){
			$post	= $model->getByIndex( 'status', 1, [], ['postId' => 'DESC'] );
			$title	= $words['panelTitles']['typeLatest'];
		}
		else if( $modeOrId ){
			$post	= $model->get( $modeOrId );
			$title	= $words['panelTitles']['typeDefault'];
		}
		if( !$post )
			return '';
		$content		= self::renderPostAbstractStatic( $env, $post, FALSE );				//  load nested page content
		$heading		= HtmlTag::create( 'h3', $title );
		$panelInner		= HtmlTag::create( 'div', $content, [
			'class'		=> 'content-panel-inner moduleInfoBlog'
		] );
		return HtmlTag::create( 'div', $heading.$panelInner, [
			'class'		=> 'content-panel content-panel-info'
		] );
	}

	/**
	 *	@param		Environment		$env
	 *	@param		object			$post
	 *	@param		bool			$showInfoBar
	 *	@return		string
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public static function renderPostAbstractStatic( Environment $env, object $post, bool $showInfoBar = TRUE ): string
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
		$linkView	= HtmlTag::create( 'a', $words->linkMore, [
			'href'	=> './info/blog/post/'.$post->postId,
		] );
		$clearfloat	= HtmlTag::create( 'div', '', ['class' => 'clearfix'] );
		$linkView	= HtmlTag::create( 'small', $linkView );
		$infobar	= View_Info_Blog::renderPostInfoBarStatic( $env, $post );
		$content	= [
			$title,
			$abstract.'&nbsp;'.$linkView.$clearfloat,
			$showInfoBar ? $infobar : '',
		];
		return HtmlTag::create( 'div', $content, ['class' => 'blog-post'] );
	}

	/**
	 *	@param		Environment		$env
	 *	@param		object			$post
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public static function renderPostInfoBarStatic( Environment $env, object $post ): string
	{
		if( !isset( $post->author ) ){
			$modelUser		= new Model_User( $env );
			$post->author	= $modelUser->get( $post->authorId );
		}
		$authorName	= $post->author->username;
		if( isset( $post->author->firstname ) && isset( $post->author->surname ) )
			$authorName	= $post->author->firstname.' '.$post->author->surname;
		$facts	= [
			'Autor: '	=> $authorName,
			'Datum: '	=> date( 'd.m.Y H:i', $post->createdAt ),
			'Gelesen: '	=> $post->nrViews.' mal',
//			'Kommentare: '	=> count( $post->comments ),
		];

		$facts		= self::renderFactsStatic( $env, $facts, 'dl-inline' );
		return HtmlTag::create( 'div', $facts, ['class' => 'infobar blog-post-info hidden-phone'] );
	}

	public static function renderPostUrlStatic( Environment $env, object $post ): string
	{
		$title	= Controller_Info_Blog::getUriPart( $post->title );
		return './info/blog/post/'.$post->postId.'-'.$title;
	}

	public function index(): void
	{
	}

	public function post(): void
	{
		$this->env->getPage()->js->addModuleFile( 'module.info.blog.js', CaptainResource::LEVEL_BOTTOM );
	}

	public function renderComment( object $comment ): string
	{
		return self::renderCommentStatic( $this->env, $comment );
	}

	protected static function renderFactsStatic( Environment $env, array $facts, string $listClass = 'dl-horizontal' ): string
	{
		$list	= [];
		foreach( $facts as $label => $value ){
			$list[]	= HtmlTag::create( 'dt', $label ).HtmlTag::create( 'dd', $value );
		}
		return HtmlTag::create( 'dl', $list, ['class' => $listClass] );
	}
}
