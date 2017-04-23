<?php


$urlBase	= $frontend->getUri().$frontend->getConfigValue( 'path.contents' ).$moduleConfig->get( 'path.covers' );

$panelCover	= '<div class="alert alert-info">Noch kein Cover-Bild hochgeladen.</div>';

if( $article->cover ){
	$id			= str_pad( $article->articleId, 5, "0", STR_PAD_LEFT );
	$class		= 'img-polaroid';
	$uriLarge	= $pathCovers.'l/'.$id.'_'.$article->cover;
	$uriMedium	= $pathCovers.'m/'.$id.'_'.$article->cover;
	$uriSmall	= $pathCovers.'s/'.$id.'_'.$article->cover;

	$urlLarge	= $urlBase.'l/'.$id.'_'.$article->cover;
	$urlMedium	= $urlBase.'m/'.$id.'_'.$article->cover;
	$urlSmall	= $urlBase.'s/'.$id.'_'.$article->cover;

	$imageMedium	= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $urlMedium, 'class' => $class ) );
	$imageSmall		= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $urlSmall, 'class' => $class ) );

	$panelCover	= '
<div class="content-panel">
	<h4>Cover-Bild</h4>
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
				<h5>Adressen</h5>
				<ul>
					<li><a target="_blank" href="'.$urlLarge.'">Vollbild</a></li>
					<li><a target="_blank" href="'.$urlMedium.'">Normalbild</a></li>
					<li><a target="_blank" href="'.$urlSmall.'">Kleinbild</a></li>
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
			<a href="./manage/catalog/bookstore/article/removeCover/'.$article->articleId.'" class="btn btn-small btn-inverse"><i class="icon-remove icon-white"></i>&nbsp;entfernen</a>
		</div>
	</div>
</div>';
}

$imageMaxSize	= Alg_UnitParser::parse( $moduleConfig->get( 'article.image.size' ), "M" );
$imageMaxSize	= Logic_Upload::getMaxUploadSize( array( 'config' => $imageMaxSize ) );
$imageMaxSize	= Alg_UnitFormater::formatBytes( $imageMaxSize );

$list				= array();
$imageExtensions	= $moduleConfig->get( 'article.image.extensions' );
foreach( explode( ",", $imageExtensions ) as $nr => $type )
	if( !in_array( trim( $type ), array( "jpe", "jpeg" ) ) )
		$list[$nr]	= strtoupper( trim( $type ) );
$imageExtensions	= join( ", ", $list );

$minSize		= $moduleConfig->get( 'article.image.medium.height' );

$panelUpload	= '
<div class="content-panel">
	<h4>Cover-Bild hochladen</h4>
	<div class="content-panel-inner form-changes-auto">
		<div class="alert">
			<b>Dateitypen: </b>
			<span>'.$imageExtensions.'</span><br/>
			<b>Dateigröße: </b>
			<span>max. '.$imageMaxSize.'</span><br/>
			<b>Bildgröße: </b>
			<span>min. '.$minSize.' Pixel hoch/breit</span>
		</div>
		<form action="./manage/catalog/bookstore/article/setCover/'.$article->articleId.'" method="post" enctype="multipart/form-data">
			<label for="input_image">Bilddatei <small class="muted"></small></label>
			'.View_Helper_Input_File::render( 'image', '<i class="icon-folder-open icon-white"></i>', 'Bild auswählen...' ).'
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-plus icon-white"></i> speichern</button>
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
?>
