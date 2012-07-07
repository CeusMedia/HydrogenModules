<?php

$roleId		= $this->env->getSession()->get( 'roleId');
$canAdd		= $roleId && $this->env->getAcl()->hasRight( $roleId, 'blog', 'add' );
$url		= './blog/add';
$label		= '&nbsp;<small>[add]</small>';
$linkAdd	= $canAdd ? UI_HTML_Elements::Link( $url, $label, 'link-ad' ) : '';


$list	= array();
foreach( $articles as $article ){
	$title		= $article->title;

	$url		= './blog/article/'.$article->articleId;
	$link		= UI_HTML_Elements::Link( $url, $title );

	$abstract	= array_shift( preg_split( "/\n/", $article->content ) );
	$abstract	= $this->formatContent( $abstract, $article->articleId );
	$abstract	= UI_HTML_Tag::create( 'div', $abstract, array( 'class' => 'blog-article' ) ).'<br/>';

	$authorList	= View_Blog::renderAuthorList( $article->authors );
	$tagList	= View_Blog::renderTagList( $article->tags );

	$content	= $link . $tagList . $abstract;
	$attributes	= array( 'class' => 'blog-article-list-item  blog-article-abstract' );
	$item		= UI_HTML_Tag::create( 'li', $content, $attributes );
	$articleList[$article->title]	= $item;
}
$articleList		= UI_HTML_Tag::create( 'ul', join( $articleList ), array( 'class' => 'blog-article-list' ) );
#$heading	= UI_HTML_Elements::Heading( 'Artikel', 3 );
$heading	= UI_HTML_Tag::create( 'h3', 'Weblog Einträge'.$linkAdd );

$helper		= new View_Helper_Pagination();
$pageList	= $helper->render( './blog/index/', $number, $limit, $page );


$list	= array();
foreach( $topTags as $relation ){
	$url	= './blog/tag/'.urlencode( urlencode( $relation->title ) );
	$link	= UI_HTML_Tag::create( 'a', $relation->title, array( 'href' => $url, 'class' => 'link-tag' ) );
	$list[]	= UI_HTML_Tag::create( 'li', $link );
} 
$listTopTags	= '[top tags]<ul class="top-tags">'.join( $list ).'</ul>';

return '
<div id="blog">
	'.$heading.'
	<div class="column-left-75">
		'.$articleList.'
		'.$pageList.'
	</div>
	<div class="column-right-25">
		'.$listTopTags.'
	</div>
	<div class="column-clear"></div>
</div>';
?>
