<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\Alg\UnitParser;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View_Manage_Catalog_Bookstore_Article $view */
/** @var Logic_Frontend $frontend */
/** @var array<string,array<string,int|string>> $words */
/** @var object $article */
/** @var string $pathCovers */
/** @var Dictionary $moduleConfig */

$urlBase	= $frontend->getUrl().$frontend->getConfigValue( 'path.contents' ).$moduleConfig->get( 'path.covers' );

$wc	= (object) $words['edit.cover'];

$panelCover	= '<div class="alert alert-info">'.$wc->noEntries.'</div>';

if( $article->cover ){
	$id			= str_pad( $article->articleId, 5, "0", STR_PAD_LEFT );
	$class		= 'img-polaroid';
	$uriLarge	= $pathCovers.'l/'.$id.'_'.$article->cover;
	$uriMedium	= $pathCovers.'m/'.$id.'_'.$article->cover;
	$uriSmall	= $pathCovers.'s/'.$id.'_'.$article->cover;

	$urlLarge	= $urlBase.'l/'.$id.'_'.$article->cover;
	$urlMedium	= $urlBase.'m/'.$id.'_'.$article->cover;
	$urlSmall	= $urlBase.'s/'.$id.'_'.$article->cover;

	$urlLarge	= 'file/bookstore/article/l/'.$article->cover;
	$urlMedium	= 'file/bookstore/article/m/'.$article->cover;
	$urlSmall	= 'file/bookstore/article/s/'.$article->cover;


	$imageMedium	= HtmlTag::create( 'img', NULL, ['src' => $urlMedium, 'class' => $class] );
	$imageSmall		= HtmlTag::create( 'img', NULL, ['src' => $urlSmall, 'class' => $class] );

	$panelCover	= '
<div class="content-panel">
	<h4>'.$wc->heading.'</h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				<div class="row-fluid">
					<div class="span6">
						<a href="'.$urlLarge.'" target="_blank" class="fancybox-auto">'.$imageMedium.'</a>
					</div>
					<div class="span5 offset1">
						'.$imageSmall.'
					</div>
				</div>
			</div>
			<div class="span6">
				<h5>'.$wc->headingUrls.'</h5>
				<ul>
					<li><a target="_blank" href="'.$urlLarge.'">'.$wc->linkFull.'</a></li>
					<li><a target="_blank" href="'.$urlMedium.'">'.$wc->linkMedium.'</a></li>
					<li><a target="_blank" href="'.$urlSmall.'">'.$wc->linkThumb.'</a></li>
				</ul>
<!--				<div class="row-fluid">
					<br/>
					<label for="input_url_image_thumb">URL des Vollbildes</label>
					<input class="span12" type="text" readonly="readonly" id="input_url_image_medium" value="'.$urlMedium.'"/><br/>
					<label for="input_url_image_thumb">URL des Kleinbildes</label>
					<input class="span12" type="text" readonly="readonly" id="input_url_image_small" value="'.$urlSmall.'"/>
				</div>-->
			</div>
		</div>
		<div class="buttonbar">
			<a href="./manage/catalog/bookstore/article/removeCover/'.$article->articleId.'" class="btn btn-small btn-inverse"><i class="icon-remove icon-white"></i>&nbsp;'.$wc->buttonRemove.'</a>
		</div>
	</div>
</div>';
}

$imageMaxSize	= UnitParser::parse( $moduleConfig->get( 'article.image.size' ), "M" );
$imageMaxSize	= Logic_Upload::getMaxUploadSize( ['config' => $imageMaxSize] );
$imageMaxSize	= UnitFormater::formatBytes( $imageMaxSize );

$list				= [];
$imageExtensions	= $moduleConfig->get( 'article.image.extensions' );
foreach( explode( ",", $imageExtensions ) as $nr => $type )
	if( !in_array( trim( $type ), ["jpe", "jpeg"] ) )
		$list[$nr]	= strtoupper( trim( $type ) );
$imageExtensions	= join( ", ", $list );

$minSize		= $moduleConfig->get( 'article.image.medium.height' );

$helperUpload	= new View_Helper_Input_File( $env );
$helperUpload->setName( 'image' );
$helperUpload->setLabel( '<i class="icon-folder-open icon-white"></i>' );
$helperUpload->setRequired( TRUE );

$panelUpload	= '
<div class="content-panel">
	<h4>'.$wc->headingUpload.'</h4>
	<div class="content-panel-inner form-changes-auto">
		<div class="alert">
			<b>'.$wc->labelFileTypes.' </b>
			<span>'.$imageExtensions.'</span><br/>
			<b>'.$wc->labelFileSize.' </b>
			<span>'.sprintf( $wc->valueFileSizeMax, $imageMaxSize ).'</span><br/>
			<b>'.$wc->labelImageSize.' </b>
			<span>'.sprintf( $wc->valueImageSizeMin, $minSize ).'</span>
		</div>
		<form action="./manage/catalog/bookstore/article/setCover/'.$article->articleId.'" method="post" enctype="multipart/form-data">
			<label for="input_image">'.$wc->labelImage.' <small class="muted"></small></label>
			'.$helperUpload.'
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-plus icon-white"></i> '.$wc->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
';

return '
<!--  Manage: Catalog: Article: Cover  -->
'.$panelCover.'
'.$panelUpload.'
<!--  /Manage: Catalog: Article: Cover  -->';
