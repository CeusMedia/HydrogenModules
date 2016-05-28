<?php
$w			= (object) $words['post'];

$data		= '<pre>'.print_m( $post, NULL, NULL, TRUE ).'</pre>';
$title		= UI_HTML_Tag::create( 'h3', $post->title );
if( strlen( $post->content ) === strlen( strip_tags( $post->content ) ) )
	$post->content  	= nl2br( $post->content );
$content	= $view->renderContent( $post->content, 'HTML' );
$infobar	= View_Info_Blog::renderPostInfoBarStatic( $env, $post );

$blogPost	= UI_HTML_Tag::create( 'div', $title.$infobar.$content, array(
	'class'		=> 'blog-post'
) );

$iconIndex	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );

$linkNext	= '';
$linkPrev	= '';
$linkIndex	= UI_HTML_Tag::create( 'a', $iconIndex.'&nbsp;'.$w->linkIndex, array(
	'href'	=> './info/blog',
	'class'	=> 'btn'
) );

if( $prevPost ){
	$label		= UI_HTML_Tag::create( 'span', $w->linkPrev, array( 'class' => 'muted' ) );
	$linkPrev	= $label.UI_HTML_Tag::create( 'a', $prevPost->title, array(
		'href'	=> View_Info_Blog::renderPostUrlStatic( $env, $prevPost ),
	) );
}
if( $nextPost ){
	$label		= UI_HTML_Tag::create( 'span', $w->linkNext, array( 'class' => 'muted' ) );
	$linkNext	= $label.UI_HTML_Tag::create( 'a', $nextPost->title, array(
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
//		$panelComments	= UI_HTML_Tag::create( 'div', 'Die Kommentarfunktion ist für diesen Eintrag nicht aktiviert.', array( 'class' => 'muted' ) ).'<br/>';
//	}
}

extract( $view->populateTexts( array( 'post.top', 'post.bottom' ), 'html/info/blog/' ) );

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