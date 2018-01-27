<?php
if( !$env->getRequest()->has( 'testing123' ) )
	return '';

if( !is_array( $relatedArticles ) || !count( $relatedArticles ) )
	return;

$limit				= 20;
$total				= count( $relatedArticles );
$relatedArticles	= array_slice( $relatedArticles, 0, $limit );
$helper				= new View_Helper_Catalog_Bookstore( $env );

$list	= array();
$tagList	= array();
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
var RelatedArticlesSlider = {
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
		var pos = $(this).scrollLeft();
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
		var options = $.extend({
			callback: function(){}
		}, options);
		var pos = RelatedArticlesSlider.pos * RelatedArticlesSlider.width;
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
?>
