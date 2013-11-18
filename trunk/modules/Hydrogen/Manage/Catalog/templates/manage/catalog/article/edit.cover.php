<?php

$listImages	= '<div class="alert alert-error">Noch kein Cover-Bild hochgeladen.</div>';

if( $article->cover ){
	$id			= str_pad( $article->articleId, 5, "0", STR_PAD_LEFT );
	$path		= "../Univerlag/contents/articles/covers/";
	$source		= $path.$id.'_'.$article->cover;
	$class		= 'img-polaroid';
	$urlFull	= "...";
	$imageFull	= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $source, 'class' => $class ) );

	$urlThumb	= "...";
	$source		= $path.$id.'__'.$article->cover;
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

return '
<div class="row-fluid">
	<div class="span6">
		<h4>Cover-Bild</h4>
		'.$listImages.'
	</div>
	<div class="span6">
		<h4>Cover-Bild hochladen</h4>
		<form action="./manage/catalog/article/setCover/'.$article->articleId.'" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_image">Bilddatei <small class="muted">(mindestens 240 Pixel hoch/breit; Typen: PNG, JPEG)</small></label>
					<input type="file" name="image" id="input_image"/>
				</div>
				<div class="buttonbar">
					<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-plus icon-white"></i> speichern</button>
				</div>
			</div>
		</form>
	</div>
</div>
';
?>
