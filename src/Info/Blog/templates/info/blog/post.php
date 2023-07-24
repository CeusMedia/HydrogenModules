<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['post'];

$data		= '<pre>'.print_m( $post, NULL, NULL, TRUE ).'</pre>';
$title		= HtmlTag::create( 'h3', $post->title );
if( strlen( $post->content ) === strlen( strip_tags( $post->content ) ) )
	$post->content  	= nl2br( $post->content );
$content	= $view->renderContent( $post->content, 'HTML' );
$infobar	= View_Info_Blog::renderPostInfoBarStatic( $env, $post );

$blogPost	= HtmlTag::create( 'div', $title.$infobar.$content, [
	'class'		=> 'blog-post'
] );

$iconIndex	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );

$linkNext	= '';
$linkPrev	= '';
$linkIndex	= HtmlTag::create( 'a', $iconIndex.'&nbsp;'.$w->linkIndex, [
	'href'	=> './info/blog',
	'class'	=> 'btn'
] );

if( $prevPost ){
	$label		= HtmlTag::create( 'span', $w->linkPrev, ['class' => 'muted'] );
	$linkPrev	= $label.HtmlTag::create( 'a', $prevPost->title, array(
		'href'	=> View_Info_Blog::renderPostUrlStatic( $env, $prevPost ),
	) );
}
if( $nextPost ){
	$label		= HtmlTag::create( 'span', $w->linkNext, ['class' => 'muted'] );
	$linkNext	= $label.HtmlTag::create( 'a', $nextPost->title, array(
		'href'	=> View_Info_Blog::renderPostUrlStatic( $env, $nextPost ),
	) );
}

$panelComment	= '';
$panelComments	= '';
if( $moduleConfig->get( 'comments' ) ){
//	if( $post->allowComments ){																		//  @todo implement entry-based comments switch
		$panelComments	= $view->loadTemplatefile( 'info/blog/comments.php' );
		$panelComment	= $view->loadTemplateFile( 'info/blog/comment.php' );
//	}
//	else{
//		$panelComments	= HtmlTag::create( 'div', 'Die Kommentarfunktion ist für diesen Eintrag nicht aktiviert.', ['class' => 'muted'] ).'<br/>';
//	}
}

extract( $view->populateTexts( ['post.top', 'post.bottom'], 'html/info/blog/' ) );

return $textPostTop.'
	<small><a href="./info/blog">'.$iconIndex.'&nbsp;'.$w->linkIndex.'</a></small>
	<div class="blog-post-view">
		'.$blogPost.'
		'.$panelComments.'
		'.$panelComment.'
	</div>
	<p>'.$linkPrev.'</p>
	<p>'.$linkNext.'</p>
	<p>'.$linkIndex.'</p>
	<br/>
'.$textPostBottom;
