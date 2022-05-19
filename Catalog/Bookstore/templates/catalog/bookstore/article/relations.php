<?php
if( !$env->getRequest()->has( 'testing123' ) )
	return '';

if( !is_array( $relatedArticles ) || !count( $relatedArticles ) )
	return;

$limit				= 20;
$total				= count( $relatedArticles );
$relatedArticles	= array_slice( $relatedArticles, 0, $limit );
$helper				= new View_Helper_Catalog_Bookstore( $env );

$list	= [];
$tagList	= [];
foreach( $tags as $tag ){
	$tagList[]	= UI_HTML_Tag::create( 'a', $tag->tag, array(
		'href'	=> $helper->getTagUri( $tag ),
		'class' => 'link-tag',
	) );
}
$tagList	= UI_HTML_Tag::create( 'span', join( ", ", $tagList ), array( 'class' => 'tag-list' ) );

foreach( $relatedArticles as $relation ){
	$title		= $relation->article->title;//Alg_Text_Trimmer::trim( $relation->article->title, 60 );
	$subtitle	= $relation->article->subtitle;//Alg_Text_Trimmer::trim( $relation->article->subtitle, 60 );
	$url		= $helper->getArticleUri( $relation->article->articleId, !TRUE );
	$image		= UI_HTML_Tag::create( 'a', $helper->renderArticleImage( $relation->article, "" ), array( 'href' => $url ) );
	$image		= UI_HTML_Tag::create( 'div', $image, array( 'class' => 'related-articles-image-container' ) );
	$title		= UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'a', $title, array( 'href' => $url ) ) );
	$sub		= UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'small', $subtitle.'&nbsp;('.$relation->matches.')', array( 'class' => '' ) ) );
	$list[]		= UI_HTML_Tag::create( 'div', array( $image, $title, $sub ), array(
		'class'		=> 'related-articles-list-item',
	) );
}

return '
<div id="related-articles" class="">
	<h3>Ähnliche Veröffentlichungen </h3>
<!--	<small>
		Weitere Veröffentlichungen <small class="muted">(insgesamt '.$total.')</small> zu den Schlagworten: '.$tagList.'
	</small>-->
	<div class="related-articles-slider">
		<div class="related-articles-container">
			<div class="related-articles-list" style="width: '.( count( $relatedArticles ) * 260 ).'px;">
				'.join( $list ).'
			</div>
		</div>
		<div class="related-articles-arrow related-articles-arrow-left" onclick="RelatedArticlesSlider.slideLeft()"><span>&lt;</span></div>
		<div class="related-articles-arrow related-articles-arrow-right" onclick="RelatedArticlesSlider.slideRight()"><span>&gt;</span></div>
	</div>
</div>
<br/>
<br/>
<script>
$(document).ready(function(){
	ModuleCatalogBookstoreRelatedArticlesSlider.init('.count( $relatedArticles ).', 260);
})
</script>';
?>
