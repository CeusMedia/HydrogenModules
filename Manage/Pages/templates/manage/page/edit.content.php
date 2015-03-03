<?php
if( $page->format === "MD" )
	unset( $words['editors']['TinyMCE'] );
$optEditor	= $words['editors'];

$helper		= new View_Helper_TinyMceResourceLister( $this->env );
$optEditor	= UI_HTML_Elements::Options( $optEditor, $editor );

$optFormat	= $words['formats'];

$optFormat	= UI_HTML_Elements::Options( $optFormat, $page->format );

$format		= $page->format === "MD" ? "Markdown" : "HTML";

$content	= '<div><small class="muted"><em>'.$words['edit']['no_editor'].'</em></small></div><br/>';
if( $page->type == 0 ){
	$content	= '
	<div class="row-fluid">
		<div class="span4">
			<label for="input_editor">'.$words['edit']['labelEditor'].'</label>
			<select class="span12" name="editor" id="input_editor">'.$optEditor.'</select>
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
		<div class="span4 pull-right text-right">
			<label>Format</label>
			<span class="muted" style="font-size: 2em">'.$format.'</span>
		</div>
	</div>
	<div class="row-fluid">
		<textarea name="content" id="input_content" class="span12" rows="20">'.htmlentities( $page->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
		<div id="hint"></div>
	</div>
	<script>
var pageType = '.(int) $page->type.';
$(document).ready(function(){
	PageEditor.editor = "'.$editor.'";
	PageEditor.format = "'.$page->format.'";
	PageEditor.linkList = '.json_encode( $helper->getLinkList() ).';
	PageEditor.imageList = '.json_encode( $helper->getImageList() ).';
});
	</script>';
}

return $content;
?>
