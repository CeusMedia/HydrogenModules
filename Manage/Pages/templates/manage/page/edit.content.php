<?php

$optEditor	= $words['editors'];

$helper		= new View_Helper_TinyMceResourceLister( $this->env );
$optEditor	= UI_HTML_Elements::Options( $optEditor, $editor );

$content	= '<div><small class="muted"><em>'.$words['edit']['no_editor'].'</em></small></div><br/>';
if( $page->type == 0 ){
	$content	= '
	<div class="row-fluid">
		<div class="span3">
			<div class="input-prepend">
				<span class="add-on">'.$words['edit']['labelEditor'].'</span>
				<select class="span12" name="editor" id="input_editor" onchange="PageEditor.setEditor(this)">'.$optEditor.'</select>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<textarea name="content" id="input_content" class="span12" rows="20">'.htmlentities( $page->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
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
