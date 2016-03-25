<?php



$list	= array();
foreach( $posts as $post ){
	$title	= UI_HTML_Tag::create( 'h4', $post->title );
	$title	= UI_HTML_Tag::create( 'a', $title, array( 'href' => './info/blog/post/'.$post->postId ) );
	$abstract	= $view->renderContent( $post->abstract );
	$linkView	= UI_HTML_Tag::create( 'a', '(weiter lesen)', array(
		'href'	=> './info/blog/post/'.$post->postId,
	) );
	$linkView	= UI_HTML_Tag::create( 'small', $linkView );
	$info		= $view->renderInfoBar( $post );
	$list[]	= UI_HTML_Tag::create( 'div', $title.$abstract.'&nbsp;'.$linkView.$info, array(
		'class'		=> 'blog-post'
	) );
}
$list	= join( $list );

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/info/blog/' ) );

return $textIndexTop.$list.$textIndexBottom;
