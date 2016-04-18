<?php

$list	= array();
foreach( $posts as $post ){
	$title	= UI_HTML_Tag::create( 'h4', $post->title );
	$title	= UI_HTML_Tag::create( 'a', $title, array( 'href' => View_Info_Blog::renderPostUrl( $post ) ) );
	$abstract	= $view->renderContent( $post->abstract );
	$linkView	= UI_HTML_Tag::create( 'a', '(weiter lesen)', array(
		'href'	=> './info/blog/post/'.$post->postId,
	) );
	$clearfloat	= UI_HTML_Tag::create( 'div', '', array( 'class' => 'clearfix' ) );
	$linkView	= UI_HTML_Tag::create( 'small', $linkView );
	$infobar	= $view->renderPostInfoBar( $post );
	$list[]		= UI_HTML_Tag::create( 'div', $title.$abstract.'&nbsp;'.$linkView.$clearfloat.$infobar, array(
		'class'		=> 'blog-post'
	) );
}
$list	= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'blog-post-list' ) );

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/info/blog/' ) );

return $textIndexTop.$list.$textIndexBottom;
