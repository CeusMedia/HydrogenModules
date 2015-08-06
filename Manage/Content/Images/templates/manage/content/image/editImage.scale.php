<?php

$w	= (object) $words['editImage.scale'];

$panelScale	= '
<div class="content-panel">
	<h4>'.$w->heading.'</h4>
	<div class="content-panel-inner">
		<form action="./manage/content/image/scale/'.base64_encode( $imagePath ).'" method="post">
			<div class="row-fluid">
				<div class="span4 input-append">
					<label for="input_width">'.$w->labelWidth.'</label>
					<input class="span7" type="text" name="width" id="input_width" value="'.$imageWidth.'"/>
					<span class="add-on">px</span>
				</div>
				<div class="span4 input-append">
					<label for="input_height">'.$w->labelHeight.'</label>
					<input class="span7" type="text" name="height" id="input_height" value="'.$imageHeight.'"/>
					<span class="add-on">px</span>
				</div>
				<div class="span4 input-append">
					<label for="input_quality">'.$w->labelQuality.'</label>
					<input class="span6" type="text" name="quality" id="input_quality" value="85"/>
					<span class="add-on">%</span>
				</div>
			</div>
			<label class="checkbox">
				<input type="checkbox" name="keepRatio" id="input_keepRatio" checked="checked" value="1"/>
				<abbr title="'.$w->labelFixRatio_title.'">'.$w->labelFixRatio.'</abbr>
			</label>
			<label class="checkbox">
				<input type="checkbox" name="copy" id="input_copy" checked="checked" value="1"/>
				<abbr title="'.$w->labelKeepOriginal_title.'">'.$w->labelKeepOriginal.'</abbr>
			</label>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn not-btn-small not-btn-info btn-primary"><i class="icon-resize-small icon-white"></i> '.$w->buttonSave.'</button>
<!--			<button type="reset" class="btn btn-mini">'.$w->buttonReset.'</button>-->
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
