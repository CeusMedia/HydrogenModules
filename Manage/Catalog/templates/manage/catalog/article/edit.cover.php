<?php


$urlBase	= $frontend->getUri().$frontend->getConfigValue( 'path.contents' ).$moduleConfig->get( 'path.covers' );

$panelCover	= '<div class="alert alert-error">Noch kein Cover-Bild hochgeladen.</div>';

if( $article->cover ){
	$id			= str_pad( $article->articleId, 5, "0", STR_PAD_LEFT );
	$class		= 'img-polaroid';
	$uriFull	= $pathCovers.$id.'_'.$article->cover;
	$uriThumb	= $pathCovers.$id.'__'.$article->cover;

	$urlFull	= $urlBase.$id.'_'.$article->cover;
	$urlThumb	= $urlBase.$id.'__'.$article->cover;

	$imageFull	= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $uriFull, 'class' => $class ) );
	$imageThumb	= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $uriThumb, 'class' => $class ) );

	$panelCover	= '
<div class="content-panel">
	<h4>Cover-Bild</h4>
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
				<h5>Adressen</h5>
				<ul>
					<li><a href="'.$urlFull.'">Vollbild</a></li>
					<li><a href="'.$urlThumb.'">Kleinbild</a></li>
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
$limits				= array( 'document' => Alg_UnitParser::parse( $documentMaxSize, "M" ) );
$documentMaxSize	= Alg_UnitFormater::formatBytes( Logic_Upload::getMaxUploadSize( $limits ) );

$list				= array();
$documentExtensions	= $moduleConfig->get( 'article.image.extensions' );
foreach( explode( ",", $documentExtensions ) as $nr => $type )
	if( !in_array( trim( $type ), array( "jpe", "jpeg" ) ) )
		$list[$nr]	= strtoupper( trim( $type ) );
$documentExtensions	= join( ", ", $list );

$minSize		= $env->getConfig()->get( 'module.manage_catalog.article.image.maxHeight' );

$panelUpload	= '
<div class="content-panel">
	<h4>Cover-Bild hochladen</h4>
	<div class="content-panel-inner form-changes-auto">
		<div class="alert">
			<b>Dateitypen: </b>
			<span>'.$documentExtensions.'</span><br/>
			<b>Dateigröße: </b>
			<span>max. '.$documentMaxSize.'</span><br/>
			<b>Bildgröße: </b>
			<span>min. '.$minSize.' Pixel hoch/breit</span>
		</div>
		<form action="./manage/catalog/article/setCover/'.$article->articleId.'" method="post" enctype="multipart/form-data">
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
