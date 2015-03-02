<?php

$optEditor	= $words['editors'];

$helper		= new View_Helper_TinyMceResourceLister( $this->env );
$optEditor	= UI_HTML_Elements::Options( $optEditor, $editor );

$optFormat	= $words['formats'];

$optFormat	= UI_HTML_Elements::Options( $optFormat, $page->format );

$content	= '<div><small class="muted"><em>'.$words['edit']['no_editor'].'</em></small></div><br/>';
if( $page->type == 0 ){
	$content	= '
	<div class="row-fluid">
		<div class="span3">
			<label for="input_format">Format</label>
			<select name="format" id="input_format" class="span12">'.$optFormat.'</select>
		</div>
		<div class="span4">
			<label for="input_editor">'.$words['edit']['labelEditor'].'</label>
			<select class="span12" name="editor" id="input_editor" onchange="PageEditor.setEditor(this)">'.$optEditor.'</select>
<!--			<div class="input-prepend">
				<span class="add-on">'.$words['edit']['labelEditor'].'</span>
				<select class="span12" name="editor" id="input_editor" onchange="PageEditor.setEditor(this)">'.$optEditor.'</select>
			</div>-->
		</div>
<!--		<div class="span3">
			<label class="checkbox">
				<input type="checkbox" name="autosave" disabled="disabled"/>
				automatisch speichern
			</label>
		</div>-->
	</div>
	<div class="row-fluid">
		<textarea name="content" id="input_content" class="span12" rows="20">'.htmlentities( $page->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
		<div id="hint"></div>
	</div>
	<script>
var pageType = '.(int) $page->type.';
$(document).ready(function(){
	PageEditor.editor = "'.$editor.'";
	PageEditor.linkList = '.json_encode( $helper->getLinkList() ).';
	PageEditor.imageList = '.json_encode( $helper->getImageList() ).';
});
	</script>';
}

return $content;
?>
