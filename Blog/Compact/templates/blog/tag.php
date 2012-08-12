<?php
$articleList	= array();
$list			= array();
foreach( $articles as $article ){
	$url	= './blog/article/'.$article->articleId.'/'.rawurlencode( $article->title );
	$link	= UI_HTML_Tag::create( 'a', $article->title, array( 'href' => $url ) );
	$list[]	= UI_HTML_Tag::create( 'li', $link );
}
$articleList	= UI_HTML_Tag::create( 'ul', join( $list ) );
$articleList	= $this->renderArticleAbstractList( $articles, FALSE, FALSE, FALSE );

$list	= array();
foreach( $friends as $friend ){
	$link	= View_Helper_Blog::renderTagLink( $env, $friend->title );
	$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => 'blog-article-tag-list-item' ) );
}
$tagList	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'class' => 'blog-article-tag-list' ) );

return '
<div id="blog">
	<div class="blog-tag">
		<h3>Artikel für Schlagwort "'.$tagName.'"</h3>
		<div class="tag-related-tags">
			verwande Schlagwörter: '.$tagList.'
		</div>
		<div>
			'.$articleList.'
		</div>
	</div>
</div>';
?>