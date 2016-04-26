<?php

$list	= array();
foreach( $posts as $post ){
	$list[]		= View_Info_Blog::renderPostAbstractStatic( $this->env, $post );
}
$list	= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'blog-post-list' ) );

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/info/blog/' ) );

return $textIndexTop.$list.$textIndexBottom;
