<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\UI\Image;

$iconCategory	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-th' ) );
$iconPrev		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconNext		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-right' ) );


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

$title		= $image->title ? $image->title : $image->filename;
$label		= HtmlTag::create( 'p', $title );
$preview	= HtmlTag::create( 'img', NULL, array(
	'src'	=> $pathPreview.$image->filename,
	'alt'	=> $title,
	'title'	=> $title,
	'class'	=> 'thumb',
) );
//$item	= HtmlTag::create( 'div', $preview, array( 'class' => 'not-span4 thumb' ) );
/*$link	= HtmlTag::create( 'a', $preview, array(
	'href'	=> './catalog/gallery/image/'.$image->galleryImageId,
	'title'	=> $title,
	'class'	=> 'thumb'
) );*/
$link	= $preview;

$articleId	= $image->galleryImageId;

$logic		= new Logic_Shop( $env );
$hasShop	= $env->getModules()->has( 'Shop' );
$isInCart	= $logic->countArticleInCart( $bridgeId, $articleId );

$buttonOrder		= "";
if( $hasShop ){
	$buttonOrder	= '<button type="button" disabled="disabled" class="btn btn btn-success"><i class="icon icon-shopping-cart icon-white"></i>&nbsp;in den Warenkorb</button>';
	if( !$isInCart )
		$buttonOrder	= '<a href="./catalog/gallery/order/'.$image->galleryImageId.'" class="btn btn btn-success"><i class="icon icon-shopping-cart icon-white"></i>&nbsp;in den Warenkorb</a>';
}

$buttonBack	= HtmlTag::create( 'a', $iconCategory, array(
	'href'		=> './catalog/gallery/category/'.$category->galleryCategoryId,
	'class'		=> 'btn not-btn-small btn-large',
	'alt'		=> 'zur Kategorie',
	'title'		=> 'zur Kategorie',
) );
$buttonPrev		= HtmlTag::create( 'a', $iconPrev, array(
	'class'		=> 'btn not-btn-small btn-large',
	'disabled'	=> 'disabled',
	'title'		=> 'zum Vorherigen',
	'alt'		=> 'zum Vorherigen',
) );
$buttonNext		= HtmlTag::create( 'a', $iconNext, array(
	'class'		=> 'btn not-btn-small btn-large',
	'disabled'	=> 'disabled',
	'title'		=> 'zum Nächsten',
	'alt'		=> 'zum Nächsten',
) );

if( $pos > 0 ){
	$imagePrev		= $images[$pos - 1];
	$buttonPrev		= HtmlTag::create( 'a', $iconPrev, array(
		'href'		=> './catalog/gallery/image/'.$imagePrev->galleryImageId,
		'class'		=> 'btn not-btn-small btn-large',
		'title'		=> 'zum Vorherigen',
		'alt'		=> 'zum Vorherigen',
	) );
}
if( $pos < count( $images ) - 1 ){
	$imageNext		= $images[$pos + 1];
	$buttonNext		= HtmlTag::create( 'a', $iconNext, array(
		'href'		=> './catalog/gallery/image/'.$imageNext->galleryImageId,
		'class'		=> 'btn not-btn-small btn-large',
		'title'		=> 'zum Nächsten',
		'alt'		=> 'zum Nächsten',
	) );
}


$source	= new Image( $pathOriginal.$image->filename );

extract( $this->populateTexts( array( 'top', 'content', 'bottom' ), 'html/catalog/gallery/image/' ) );

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
					<dt>Titel</dt>
					<dd>'.$image->title.'</dd>
					<dt>Dateiname</dt>
					<dd>'.$image->filename.'</dd>
					<dt>Dateigröße</dt>
					<dd>'.Alg_UnitFormater::formatBytes( filesize( $pathOriginal.$image->filename ) ).'</dd>
					<dt>Auflösung</dt>
					<dd>'.$source->getWidth().' × '.$source->getHeight().'px</dd>
					<dt>Preis</dt>
					<dd>
						'.number_format( $image->price, 2, ',', NULL ).'&nbsp;&euro;<br/>
						<small class="muted">inklusive '.$tax.'% MwSt.</small>
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
'.$textBottom.'
';


?>
