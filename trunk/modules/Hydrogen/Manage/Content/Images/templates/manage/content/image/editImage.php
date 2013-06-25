<?php

$thumbnailer	= new View_Helper_Thumbnailer( $env );
$imageSource	= $thumbnailer->get( $pathImages.$imagePath );
$megaPixel		= round( $imageWidth * $imageHeight / 1024 / 1024, 1 );
$optFolder	= array( '.' => "" );
foreach( $folders as $folder )
	$optFolder[$folder]	= $folder;
$optFolder	= UI_HTML_Elements::Options( $optFolder, dirname( $path ) );

$listFolders	= $view->listFolders( dirname( $imagePath ) );

return '
<style>
dl.dl-horizontal dt {
	width: 100px;
	text-align: left;
	}
dl.dl-horizontal dd {
	margin-left: 120px;
	}
</style>
<div class="row-fluid">
	<div class="span3">
		<h4>Ordner</h4>
		'.$listFolders.'
		<a href="./manage/content/image/addFolder?path='.$path.'" class="btn btn-info btn-small"><i class="icon-plus icon-white"></i> neuer Ordner</a>
	</div>
	<div class="span9">
		<h4><span class="muted">Bild: </span>'.$imageName.'</h4> 
		<div class="row-fluid">
			<div class="span9">
				<h4>Informationen</h4>
				<div class="row-fluid">
					<div class="span8">
						<dl class="dl-horizontal" style="margin: 0px">
							<dt>Dateiname</dt>
							<dd>'.$imageName.'</dd>
							<dt>Ordner</dt>
							<dd>'.$folderPath.'</dd>
							<dt>Datum</dt>
							<dd>'.date( 'd.m.Y H:i:s', $filetime ).'</dd>
							<dt>Dateigröße</dt>
							<dd>'.Alg_UnitFormater::formatBytes( $filesize ).'</dd>
							<dt>Dateityp</dt>
							<dd>'.$mimetype.'</dd>
							<dt>Bildgröße</dt>
							<dd>'.$megaPixel.' <abbr title="Megapixel">MP</abbr> <small class="muted">('.$imageWidth.' x '.$imageHeight.' Pixel)</small></dd>
							<dt>URL</dt>
							<dd><small><a href="'.$frontUrl.substr( $folderPath, 2 ).$imageName.'">'.$frontUrl.substr( $folderPath, 2 ).$imageName.'</a></small></dd>
						</dl>
					</div>
					<div class="span4">
						<a href="./manage/content/image/view?path='.$path.'" target="_blank">
							<img src="'.$imageSource.'" class="thumbnail"/>
						</a>
					</div>
				</div>
				<br/>
				<div class="row-fluid">
					<div class="span7">
						<h4>Bild umbenennen oder verschieben</h4>
						<form action="./manage/content/image/editImage?path='.$imagePath.'" method="post">
							<label for="input_filename">Dateiname</label>
							<input class="span11" type="text" name="filename" id="input_filename" value="'.htmlentities( $imageName, ENT_QUOTES, 'UTF-8' ).'"/>
							<label for="input_folderpath">Ordner</label>
							<select class="span11" name="folderpath" id="input_folderpath">'.$optFolder.'</select>
							<div class="buttonbar">
								<a class="btn btn-small" href="./manage/content/image?path='.dirname( $imagePath ).'"><i class="icon-arrow-left"></i> zurück</a>
								<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
								<button type="button" class="btn btn-small btn-danger" onclick="if(confirm(\'Wirklich?\'))document.location.href=\'./manage/content/image/removeImage?path='.$imagePath.'\';"><i class="icon-remove icon-white"></i> entfernen</a>
							</div>
						</form>
					</div>
					<div class="span5">
						<h4>Bildgröße ändern</h4>
						<form action="./manage/content/image/scale?path='.$imagePath.'" method="post">
							<div class="row-fluid">
								<div class="span4 input-append">
									<label for="input_width">Breite</label>
									<input class="span7" type="text" name="width" id="input_width" value="'.$imageWidth.'"/>
									<span class="add-on">px</span>
								</div>
								<div class="span4 input-append">
									<label for="input_height">Höhe</label>
									<input class="span7" type="text" name="height" id="input_height" value="'.$imageHeight.'"/>
									<span class="add-on">px</span>
								</div>
								<div class="span4 input-append">
									<label for="input_quality">Qualität</label>
									<input class="span6" type="text" name="quality" id="input_quality" value="85"/>
									<span class="add-on">%</span>
								</div>
							</div>
							<label class="checkbox">
								<input type="checkbox" name="keepRatio" id="input_keepRatio" checked="checked" value="1"/>
								<abbr title="Bei Änderung der Breite/Höhe wird die Höhe/Breite automatisch berechnet, um die natürlichen Proportionen des Bildes zu erhalten.">Größenverhältnis beibehalten</abbr>
							</label>
							<label class="checkbox">
								<input type="checkbox" name="copy" id="input_copy" checked="checked" value="1"/>
								<abbr title="Das skalierte Bild wird unter einem neuen Namen gespeichert. Die alte Bilddatei bleibt erhalten. Anderenfalls wird sie mit dem neuen Bild überschrieben.">Originalbild erhalten</abbr>
							</label>
							<div class="buttonbar">
								<button type="submit" name="save" class="btn btn-small btn-info"><i class="icon-resize-small icon-white"></i> ändern</button>
<!--								<button type="reset" class="btn btn-mini">zurücksetzen</button>-->
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="span3">
				<h4>Anleitung</h4>
				<p>
				</p>
				<div class="alert alert-error"><b>Achtung</b> falls Bild bereits verlinkt wurde!</div>
				<p>
					Wenn die Bilddatei umbenannt oder verschoben wird, kann das zu Fehler bei der Darstellung der Webseite führen.<br/>
						Bitte stelle sicher, dass alle Stellen, wo die Bilddatei verlinkt wurde, korrigiert werden.
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
var imageRatio;
var inputWidth;
var inputHeight;
var inputRatio;
$(document).ready(function(){
	imageRatio = parseInt($("#input_width").val()) / parseInt($("#input_height").val());
	inputWidth = $("#input_width");
	inputHeight = $("#input_height");
	inputRatio = $("#input_keepRatio");
	inputWidth.bind("not-change keyup", function(){
		inputWidth.val(inputWidth.val().replace(/[^0-9]/, ""));
		if(inputRatio.attr("checked")){
			inputHeight.val(Math.round(inputWidth.val()/imageRatio));
		}
	});
	inputHeight.bind("not-change keyup", function(){
		inputHeight.val(inputHeight.val().replace(/[^0-9]/, ""));
		if(inputRatio.attr("checked")){
			inputWidth.val(Math.round(inputHeight.val()*imageRatio));
		}
	});
	inputRatio.bind("change", function(){
		if(inputRatio.attr("checked"))
			inputWidth.trigger("keyup");
	})
});

</script>

';
?>
