<?php

$data		= '<pre>'.print_m( $post, NULL, NULL, TRUE ).'</pre>';
$title		= UI_HTML_Tag::create( 'h3', $post->title );
if( strlen( $post->content ) === strlen( strip_tags( $post->content ) ) )
	$post->content  	= nl2br( $post->content );
$content	= $view->renderContent( $post->content, 'HTML' );
$infobar	= $view->renderInfoBar( $post );

$container	= UI_HTML_Tag::create( 'div', $title.$infobar.$content, array(
		'class'		=> 'blog-post'
) );

extract( $view->populateTexts( array( 'post.top', 'post.bottom' ), 'html/info/blog/' ) );

return $textPostTop.$container.$data.$textPostBottom;
