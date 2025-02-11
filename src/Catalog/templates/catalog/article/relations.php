<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $words */
/** @var array $relatedArticles */
/** @var array $tags */

if( !$env->getRequest()->has( 'testing123' ) )
	return '';

if( !is_array( $relatedArticles ) || !count( $relatedArticles ) )
	return;

$limit				= 20;
$total				= count( $relatedArticles );
$relatedArticles	= array_slice( $relatedArticles, 0, $limit );
$helper				= new View_Helper_Catalog( $env );

$list	= [];
$tagList	= [];
foreach( $tags as $tag ){
	$tagList[]	= HtmlTag::create( 'a', $tag->tag, [
		'href'	=> $helper->getTagUri( $tag ),
		'class' => 'link-tag',
	] );
}
$tagList	= HtmlTag::create( 'span', join( ", ", $tagList ), ['class' => 'tag-list'] );

foreach( $relatedArticles as $relation ){
	$title		= $relation->article->title;//TextTrimmer::trim( $relation->article->title, 60 );
	$subtitle	= $relation->article->subtitle;//TextTrimmer::trim( $relation->article->subtitle, 60 );
	$url		= $helper->getArticleUri( $relation->article->articleId );
	$image		= HtmlTag::create( 'a', $helper->renderArticleImage( $relation->article ), ['href' => $url] );
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
let RelatedArticlesSlider = {
	pos: 0,
	animating: false,
	init: function(number, width){
		RelatedArticlesSlider.number = number;
		RelatedArticlesSlider.width = width;
		$(".related-articles-list").width(number * width);
		$(".related-articles-container").scroll(RelatedArticlesSlider.onScroll);
		RelatedArticlesSlider.updateArrows();
	},
	onScroll: function(container){
		if(RelatedArticlesSlider.animating)
			return;
		let pos = $(this).scrollLeft();
		RelatedArticlesSlider.pos = Math.round(pos / RelatedArticlesSlider.width);
		RelatedArticlesSlider.updateArrows();
	},
	updateArrows: function(){
		if(RelatedArticlesSlider.pos === 0)
			$(".related-articles-arrow-left").stop(true).animate({opacity: 0.25});
		else
			$(".related-articles-arrow-left").stop(true).animate({opacity: 1});

		if(RelatedArticlesSlider.pos + 3 === RelatedArticlesSlider.number)
			$(".related-articles-arrow-right").stop(true).animate({opacity: 0.25});
		else
			$(".related-articles-arrow-right").stop(true).animate({opacity: 1});
	},
	slideToCurrentPosition: function(options){
		let options = $.extend({
			callback: function(){}
		}, options);
		let pos = RelatedArticlesSlider.pos * RelatedArticlesSlider.width;
		RelatedArticlesSlider.animating = true;
		RelatedArticlesSlider.updateArrows();
		$(".related-articles-container").stop(true).animate({scrollLeft: pos}, {complete: function(){
			RelatedArticlesSlider.animating = false;
			options.callback();
		}});
	},
	slideLeft: function(){
		if(RelatedArticlesSlider.pos > 0){
			RelatedArticlesSlider.pos--;
			RelatedArticlesSlider.slideToCurrentPosition();
		}
	},
	slideRight: function(){
		if(RelatedArticlesSlider.pos + 3 < RelatedArticlesSlider.number){
			RelatedArticlesSlider.pos++;
			RelatedArticlesSlider.slideToCurrentPosition();
		}
	}
};
$(document).ready(function(){
	RelatedArticlesSlider.init('.count( $relatedArticles ).', 260);
})
</script>
<style>
.related-articles-slider {
	position: relative;
	height: 220px;
	box-sizing: box-model;
	}
.related-articles-slider * {
	-webkit-user-select: none;	/* Chrome all / Safari all */
	-moz-user-select: none;		/* Firefox all */
	-ms-user-select: none;		/* IE 10+ */
	user-select: none;
	}
.related-articles-slider .related-articles-arrow {
	position: absolute;
	top: 0;
	left: 0;
	width: 5%;
	height: 218px;
	border: 1px solid rgba(127,127,127,0.2);
	background-color: rgba(127,127,127,0.1);
	vertical-align: middle;
	text-align: center;
	display: table-cell;
	vertical-align: middle;
	cursor: pointer;
	}
.related-articles-slider .related-articles-arrow span {
	line-height: 200px;
	font-size: 2em;
	font-weight: bold;
	}
.related-articles-slider .related-articles-arrow-right {
	left: auto;
	right: 0;
	}
.related-articles-slider .related-articles-container {
	position: absolute;
	left: 5%;
	right: 5%;
	top: 0;
	width: 90%;
	height: 100%;
/*	border: 1px solid rgba(127,127,127,0.5);*/
	overflow: hidden;
	overflow-x: auto;
	}
.related-articles-slider .related-articles-list {
	position: relative;
	white-space: nowrap;
	}
.related-articles-slider .related-articles-list .related-articles-list-item {
	float: left;
	width: 240px;
	height: 200px;
	padding: 10px;
	text-align: center;
	white-space: initial;
	overflow: hidden;
/*	overflow-y: auto;*/
	}
.related-articles-slider .related-articles-list .related-articles-list-item:hover {
	background-color: yellow;
	background-color: #F7F7F7;
	}
.related-articles-slider .related-articles-image-container {
	padding-bottom: 0.5em;
	}
.related-articles-slider .related-articles-list img {
	height: 120px;
	}
</style>';
