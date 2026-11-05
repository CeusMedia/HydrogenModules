<?php

use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\Alg\UnitParser;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$urlBase	= $frontend->getUri().$frontend->getConfigValue( 'path.contents' ).$moduleConfig->get( 'path.covers' );

$wc	= (object) $words['edit.cover'];

$panelCover	= '<div class="alert alert-error">'.$wc->noEntries.'</div>';

if( $article->cover ){
	$id			= str_pad( $article->articleId, 5, "0", STR_PAD_LEFT );
	$class		= 'img-polaroid';
	$uriFull	= $pathCovers.$id.'_'.$article->cover;
	$uriThumb	= $pathCovers.$id.'__'.$article->cover;

	$urlFull	= $urlBase.$id.'_'.$article->cover;
	$urlThumb	= $urlBase.$id.'__'.$article->cover;

	$imageFull	= HtmlTag::create( 'img', NULL, ['src' => $uriFull, 'class' => $class] );
	$imageThumb	= HtmlTag::create( 'img', NULL, ['src' => $uriThumb, 'class' => $class] );

	$panelCover	= '
<div class="content-panel">
	<h4>'.$wc->heading.'</h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				<div class="row-fluid">
					<div class="span6">
						<a href="'.$urlFull.'" target="_blank" class="fancybox-auto">'.$imageFull.'</a>
					</div>
					<div class="span5 offset1">
						'.$imageThumb.'
					</div>
				</div>
			</div>
			<div class="span6">
				<h5>'.$wc->headingUrls.'</h5>
				<ul>
					<li><a href="'.$urlFull.'">'.$wc->linkFull.'</a></li>
					<li><a href="'.$urlThumb.'">'.$wc->linkThumb.'</a></li>
				</ul>
<!--				<div class="row-fluid">
					<br/>
					<label for="input_url_image_thumb">URL des Vollbildes</label>
					<input class="span12" type="text" readonly="readonly" id="input_url_image_full" value="'.$urlFull.'"/><br/>
					<label for="input_url_image_thumb">URL des Kleinbildes</label>
					<input class="span12" type="text" readonly="readonly" id="input_url_image_thumb" value="'.$urlThumb.'"/>
				</div>-->
			</div>
		</div>
	</div>
</div>';
}

$documentMaxSize	= $moduleConfig->get( 'article.document.maxSize' );
$limits				= ['document' => UnitParser::parse( $documentMaxSize, "M" )];
$documentMaxSize	= UnitFormater::formatBytes( Logic_Upload::getMaxUploadSize( $limits ) );

$list				= [];
$documentExtensions	= $moduleConfig->get( 'article.image.extensions' );
foreach( explode( ",", $documentExtensions ) as $nr => $type )
	if( !in_array( trim( $type ), ["jpe", "jpeg"] ) )
		$list[$nr]	= strtoupper( trim( $type ) );
$documentExtensions	= join( ", ", $list );

$minSize		= $env->getConfig()->get( 'module.manage_catalog.article.image.maxHeight' );

$panelUpload	= '
<div class="content-panel">
	<h4>'.$wc->headingUpload.'</h4>
	<div class="content-panel-inner form-changes-auto">
		<div class="alert">
			<b>'.$wc->labelFileTypes.' </b>
			<span>'.$documentExtensions.'</span><br/>
			<b>'.$wc->labelFileSize.' </b>
			<span>'.sprintf( $wc->valueFileSizeMax, $documentMaxSize ).'</span><br/>
			<b>'.$wc->labelImageSize.' </b>
			<span>'.sprintf( $wc->valueImageSizeMin, $minSize ).'</span>
		</div>
		<form action="./manage/catalog/article/setCover/'.$article->articleId.'" method="post" enctype="multipart/form-data">
			<label for="input_image">'.$wc->labelImage.' <small class="muted"></small></label>
			'.View_Helper_Input_File::renderStatic( $env, 'image', '<i class="icon-folder-open icon-white"></i>', $wc->buttonSelect ).'
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
