<?php
$content	= $this->loadContentFile( 'html/index/content.html' );

$env->getPage()->js->addScriptOnReady( '
var saveUrl = "ace/save";
var editor = jQuery("#input_content").data("ace-editor");
ModuleAceAutoSave.applyToEditor(editor, saveUrl);
' );

return '
<div class="row-fluid">
	<div class="span12">
		<textarea id="input_content" class="span12 ace-auto" rows="20">'.$content.'</textarea>
	</div>
</div>';
?>
