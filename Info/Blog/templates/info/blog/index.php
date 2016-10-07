<?php

$list	= array();
foreach( $posts as $post ){
	$list[]		= View_Info_Blog::renderPostAbstractStatic( $this->env, $post );
}
$list	= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'blog-post-list' ) );

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/info/blog/' ) );

return $textIndexTop.'
<div class="content-panel content-panel-list">
	<h3>Blog-Beiträge</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>
'.$textIndexBottom;
