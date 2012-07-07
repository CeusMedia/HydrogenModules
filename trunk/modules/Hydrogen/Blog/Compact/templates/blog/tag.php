<?php
$articleList	= array();
$list			= array();
foreach( $articles as $article ){
	$url	= './blog/article/'.$article->articleId.'/'.urlencode( $article->title );
	$link	= UI_HTML_Tag::create( 'a', $article->title, array( 'href' => $url ) );
	$list[]	= UI_HTML_Tag::create( 'li', $link );
}
$articleList	= UI_HTML_Tag::create( 'ul', join( $list ) );

$list	= array();
foreach( $friends as $friend ){
	$url	= './blog/tag/'.urlencode( urlencode( $friend->title ) );
	$link	= UI_HTML_Tag::create( 'a', $friend->title, array( 'href' => $url, 'class' => 'link-tag' ) );
	$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => 'blog-article-tag-list-item' ) );
}
$tagList	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'class' => 'blog-article-tag-list' ) );
return '
<div id="blog">
	<div class="blog-tag">
		<h3>Artikel für Schlagwort "'.$tagName.'"</h3>
		<div class="">
			verwande Schlagwörter: '.$tagList.'
		</div>
		<div>
			'.$articleList.'
		</div>
	</div>
</div>
';
?>
