<?php

use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\UI\Image;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var object[] $images */
/** @var object $category */
/** @var string $pathImages */
/** @var int $bridgeId */
/** @var ?object $image */
/** @var array $words */
/** @var int|float $tax */

$w	= (object) $words['image'];

$iconCategory	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-th'] );
$iconPrev		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconNext		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] );

$pos	= -1;
foreach( $images as $nr => $item ){
	if( $item->galleryImageId == $image->galleryImageId ){
		$pos	= $nr;
		break;
	}
}

$list			= [];
$pathPreview	= $pathImages.'preview/'.$category->path.'/';
$pathOriginal	= $pathImages.'original/'.$category->path.'/';

$title		= $image->title ?: $image->filename;
$label		= HtmlTag::create( 'p', $title );
$preview	= HtmlTag::create( 'img', NULL, [
	'src'	=> $pathPreview.$image->filename,
	'alt'	=> $title,
	'title'	=> $title,
	'class'	=> 'thumb',
] );
//$item	= HtmlTag::create( 'div', $preview, ['class' => 'not-span4 thumb'] );
/*$link	= HtmlTag::create( 'a', $preview, [
	'href'	=> './catalog/gallery/image/'.$image->galleryImageId,
	'title'	=> $title,
	'class'	=> 'thumb'
] );*/
$link	= $preview;

$articleId	= $image->galleryImageId;

$logic		= new Logic_Shop( $env );
$hasShop	= $env->getModules()->has( 'Shop' );
$isInCart	= $logic->countArticleInCart( $bridgeId, $articleId );

$buttonOrder		= "";
if( $hasShop ){
	$buttonOrder	= '<button type="button" disabled="disabled" class="btn btn btn-success"><i class="icon icon-shopping-cart icon-white"></i>&nbsp;'.$w->buttonCart.'</button>';
	if( !$isInCart )
		$buttonOrder	= '<a href="./catalog/gallery/order/'.$image->galleryImageId.'" class="btn btn btn-success"><i class="icon icon-shopping-cart icon-white"></i>&nbsp;'.$w->buttonCart.'</a>';
}

$buttonBack	= HtmlTag::create( 'a', $iconCategory, [
	'href'		=> './catalog/gallery/category/'.$category->galleryCategoryId,
	'class'		=> 'btn not-btn-small btn-large',
	'alt'		=> $w->buttonCategory,
	'title'		=> $w->buttonCategory,
] );
$buttonPrev		= HtmlTag::create( 'a', $iconPrev, [
	'class'		=> 'btn not-btn-small btn-large',
	'disabled'	=> 'disabled',
	'title'		=> $w->buttonPrev,
	'alt'		=> $w->buttonPrev,
] );
$buttonNext		= HtmlTag::create( 'a', $iconNext, [
	'class'		=> 'btn not-btn-small btn-large',
	'disabled'	=> 'disabled',
	'title'		=> $w->buttonNext,
	'alt'		=> $w->buttonNext,
] );

if( $pos > 0 ){
	$imagePrev		= $images[$pos - 1];
	$buttonPrev		= HtmlTag::create( 'a', $iconPrev, [
		'href'		=> './catalog/gallery/image/'.$imagePrev->galleryImageId,
		'class'		=> 'btn not-btn-small btn-large',
		'title'		=> $w->buttonPrev,
		'alt'		=> $w->buttonPrev,
	] );
}
if( $pos < count( $images ) - 1 ){
	$imageNext		= $images[$pos + 1];
	$buttonNext		= HtmlTag::create( 'a', $iconNext, [
		'href'		=> './catalog/gallery/image/'.$imageNext->galleryImageId,
		'class'		=> 'btn not-btn-small btn-large',
		'title'		=> $w->buttonNext,
		'alt'		=> $w->buttonNext,
	] );
}


$source	= new Image( $pathOriginal.$image->filename );

extract( $this->populateTexts( ['top', 'content', 'bottom'], 'html/catalog/gallery/image/' ) );

return '
'.$textTop.'
<div class="row-fluid" id="catalog-gallery-view">
	<div class="span3 not-pull-right">
		<h3>'.$words['categories']['heading'].'</h3>
		'.$categoryList.'
	</div>
	<div class="span9 pull-left">
		<h3><span class="muted">'.$category->title.': </span>'.$image->title.'</h3>
		<div class="row-fluid">
			<div class="span8" id="catalog-gallery-view-image">
				<figure class="img-polaroid">
					'.$link.'
					<figcaption style="text-align: center">
						'.$image->title.'
					</figcaption>
				</figure>
				<div class="buttonbar">
					<div class="btn-group">
						'.$buttonPrev.'
						'.$buttonBack.'
						'.$buttonNext.'
					</div>
				</div>
			</div>
			<div class="span3" id="catalog-gallery-view-facts">
				<dl class="facts">
					<dt>'.$w->labelTitle.'</dt>
					<dd>'.$image->title.'</dd>
					<dt>'.$w->labelFilename.'</dt>
					<dd>'.$image->filename.'</dd>
					<dt>'.$w->labelFileSize.'</dt>
					<dd>'.UnitFormater::formatBytes( filesize( $pathOriginal.$image->filename ) ).'</dd>
					<dt>'.$w->labelResolution.'</dt>
					<dd>'.$source->getWidth().' × '.$source->getHeight().'px</dd>
					<dt>'.$w->labelPrice.'</dt>
					<dd>
						'.number_format( $image->price, 2, ',', NULL ).'&nbsp;&euro;<br/>
						<small class="muted">'.sprintf( $w->textTax, $tax ).'</small>
					</dd>
				</dl>
				<div class="buttonbar">
					'.$buttonOrder.'
				</div>
			</div>
		</div>
	</div>
</div>
<br/>
'.$textBottom;
