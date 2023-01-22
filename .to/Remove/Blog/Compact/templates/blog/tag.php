<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$articleList	= [];
$list			= [];
foreach( $articles as $article ){
	$url	= './blog/article/'.$article->articleId.'/'.rawurlencode( $article->title );
	$link	= HtmlTag::create( 'a', $article->title, ['href' => $url] );
	$list[]	= HtmlTag::create( 'li', $link );
}
$articleList	= HtmlTag::create( 'ul', join( $list ) );
$articleList	= $this->renderArticleAbstractList( $articles, FALSE, FALSE, FALSE );

$list	= [];
foreach( $friends as $friend ){
	$link	= View_Helper_Blog::renderTagLink( $env, $friend->title );
	$list[]	= HtmlTag::create( 'li', $link, ['class' => 'blog-article-tag-list-item'] );
}
$tagList	= HtmlTag::create( 'ul', join( $list ), ['class' => 'blog-article-tag-list'] );

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