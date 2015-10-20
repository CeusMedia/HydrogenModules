<?php

$listImages	= '<div class="alert alert-error">Noch kein Cover-Bild hochgeladen.</div>';

$moduleConfig	= $config->getAll( 'module.manage_catalog.', TRUE );
$imagePath		= $moduleConfig->get( 'path.frontend' ).$moduleConfig->get( 'path.frontend.covers' );


if( $article->cover ){
	$id			= str_pad( $article->articleId, 5, "0", STR_PAD_LEFT );
	$source		= $imagePath.$id.'_'.$article->cover;
	$class		= 'img-polaroid';
	$urlFull	= "...";
	$imageFull	= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $source, 'class' => $class ) );

	$urlThumb	= "...";
	$source		= $imagePath.$id.'__'.$article->cover;
	$imageThumb	= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $source, 'class' => $class ) );

	$listImages	= '
		<div class="row-fluid">
			<div class="span6">
				'.$imageFull.'
			</div>
			<div class="span6">
				'.$imageThumb.'
			</div>
		</div>
		<div class="row-fluid">
			<br/>
			<label for="input_url_image_thumb">URL des Vollbildes</label>
			<input class="span12" type="text" readonly="readonly" id="input_url_image_full" value="'.$urlFull.'"/><br/>
			<label for="input_url_image_thumb">URL des Kleinbildes</label>
			<input class="span12" type="text" readonly="readonly" id="input_url_image_thumb" value="'.$urlThumb.'"/>
		</div>
	';
}

$documentMaxSize	= $env->getConfig()->get( 'module.manage_catalog.article.document.maxSize' );
$limits				= array( 'document' => Alg_UnitParser::parse( $documentMaxSize, "M" ) );
$documentMaxSize	= Alg_UnitFormater::formatBytes( Logic_Upload::getMaxUploadSize( $limits ) );

$list				= array();
$documentExtensions	= $env->getConfig()->get( 'module.manage_catalog.article.image.extensions' );
foreach( explode( ",", $documentExtensions ) as $nr => $type )
	if( !in_array( trim( $type ), array( "jpe", "jpeg" ) ) )
		$list[$nr]	= strtoupper( trim( $type ) );
$documentExtensions	= join( ", ", $list );

$minSize		= $env->getConfig()->get( 'module.manage_catalog.article.image.maxHeight' );

return '
<!--  Manage: Catalog: Article: Cover  -->
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h4>Cover-Bild</h4>
			<div class="content-panel-inner">
				'.$listImages.'
			</div>
		</div>
	</div>
	<div class="span6">
		<div class="content-panel">
			<h4>Cover-Bild hochladen</h4>
			<div class="content-panel-inner">

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
	</div>
</div>
<!--  /Manage: Catalog: Article: Cover  -->';
?>
