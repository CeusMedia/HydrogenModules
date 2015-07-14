<?php

$panelScale	= '
<div class="content-panel">
	<h4>Bildgröße ändern</h4>
	<div class="content-panel-inner">
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
<!--			<button type="reset" class="btn btn-mini">zurücksetzen</button>-->
			</div>
		</form>
	</div>
</div>';

$script	= '<script>
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
</script>';

return $panelScale.$script;
?>
