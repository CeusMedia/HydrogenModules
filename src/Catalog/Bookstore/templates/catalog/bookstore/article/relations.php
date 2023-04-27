<?php

use CeusMedia\Common\Alg\Text\Trimmer as TextTrimmer;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var Environment $env */
/** @var array $words */
/** @var object[] $tags */
/** @var object[] $relatedArticles */

if( !$env->getRequest()->has( 'testing123' ) )
	return '';

if( !is_array( $relatedArticles ) || !count( $relatedArticles ) )
	return;

$limit				= 20;
$total				= count( $relatedArticles );
$relatedArticles	= array_slice( $relatedArticles, 0, $limit );
$helper				= new View_Helper_Catalog_Bookstore( $env );

$list		= [];
$tagList	= [];
foreach( $tags as $tag ){
	$tagList[]	= HtmlTag::create( 'a', $tag->tag, array(
		'href'	=> $helper->getTagUri( $tag ),
		'class' => 'link-tag',
	) );
}
$tagList	= HtmlTag::create( 'span', join( ", ", $tagList ), ['class' => 'tag-list'] );

foreach( $relatedArticles as $relation ){
	$title		= $relation->article->title;//TextTrimmer::trim( $relation->article->title, 60 );
	$subtitle	= $relation->article->subtitle;//TextTrimmer::trim( $relation->article->subtitle, 60 );
	$url		= $helper->getArticleUri( $relation->article->articleId, !TRUE );
	$image		= HtmlTag::create( 'a', $helper->renderArticleImage( $relation->article, "" ), ['href' => $url] );
	$image		= HtmlTag::create( 'div', $image, ['class' => 'related-articles-image-container'] );
	$title		= HtmlTag::create( 'div', HtmlTag::create( 'a', $title, ['href' => $url] ) );
	$sub		= HtmlTag::create( 'div', HtmlTag::create( 'small', $subtitle.'&nbsp;('.$relation->matches.')', ['class' => ''] ) );
	$list[]		= HtmlTag::create( 'div', [$image, $title, $sub], [
		'class'		=> 'related-articles-list-item',
	] );
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
