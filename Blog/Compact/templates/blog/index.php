<?php

$roleId		= $this->env->getSession()->get( 'roleId');
$canAdd		= $roleId && $this->env->getAcl()->hasRight( $roleId, 'blog', 'add' );
$url		= './blog/add';
$label		= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/add.png', 'neuer Eintrag' );
$linkAdd	= $canAdd ? UI_HTML_Elements::Link( $url, $label, 'button link-add' ) : '';

$list	= array();
foreach( $articles as $article ){
	$title		= $article->title;

	$url		= './blog/article/'.$article->articleId;
	if( $config->get( 'niceURLs' ) )
		$url	.= '-'.View_Helper_Blog::getArticleTitleUrlLabel( $article );
	$link		= UI_HTML_Elements::Link( $url, $title );

	$abstract	= array_shift( preg_split( "/\n/", $article->content ) );
	$abstract	= $this->formatContent( $abstract, $article->articleId );
	$abstract	= UI_HTML_Tag::create( 'div', $abstract, array( 'class' => 'blog-article' ) );

	$authorList	= View_Blog::renderAuthorList( $article->authors );
	$tagList	= View_Blog::renderTagList( $article->tags );

	$content	= $link . $tagList . $abstract;
	$attributes	= array( 'class' => 'blog-article-list-item  blog-article-abstract' );
	$item		= UI_HTML_Tag::create( 'li', $content, $attributes );
	$articleList[$article->title]	= $item;
}
$articleList		= UI_HTML_Tag::create( 'ul', join( $articleList ), array( 'class' => 'blog-article-list' ) );
#$heading	= UI_HTML_Elements::Heading( 'Artikel', 3 );
$heading	= UI_HTML_Tag::create( 'h3', 'Blog-Einträge'.$linkAdd );

$helper		= new View_Helper_Pagination();
$pageList	= $helper->render( './blog/index/', $number, $limit, $page );


$list	= array();
foreach( $topTags as $relation ){
	$url	= './blog/tag/'.urlencode( urlencode( $relation->title ) );
	$link	= UI_HTML_Tag::create( 'a', $relation->title, array( 'href' => $url, 'class' => 'link-tag' ) );
	$list[]	= UI_HTML_Tag::create( 'li', $link );
} 
$listTopTags	= '<h4>Häufige Schlüsselwörter</h4><ul class="top-tags">'.join( $list ).'</ul>';

return '
<div id="blog">
	'.$heading.'
	<div class="column-left-70">
		'.$articleList.'
		'.$pageList.'
	</div>
	<div class="column-right-25">
		'.$listTopTags.'
	</div>
	<div class="column-clear"></div>
</div>';
?>
