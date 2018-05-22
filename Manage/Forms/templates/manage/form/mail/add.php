<?php
$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$optFormat	= array(
	0	=> 'nicht definiert',
	1	=> 'Text',
	2	=> 'HTML',
);
$optFormat	= UI_HTML_Elements::Options( $optFormat, Model_Form_Mail::FORMAT_TEXT );

return '
<h2><span class="muted">Mail:</span> Neu</h2>
<form action="./manage/form/mail/add" method="post">
	<div class="row-fluid">
		<div class="span8">
			<label for="input_title">Titel</label>
			<input type="text" name="title" id="input_title" class="span12"/>
		</div>
		<div class="span4">
			<label for="input_identifier">Shortcode</label>
			<input type="text" name="identifier" id="input_identifier" class="span12"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span4">
			<label for="input_subject">Betreff</label>
			<input type="text" name="subject" id="input_subject" class="span12"/>
		</div>
		<div class="span4">
			<label for="input_format">Format</label>
			<select name="format" id="input_format" class="span12">'.$optFormat.'</select>
		</div>
	</div>
	<div class="row-fluid" style="margin-bottom: 1em">
		<div class="span12">
			<label for="input_content">Inhalt</label>
			<textarea name="content" id="input_content" class="span12" rows="20"></textarea>
			<div id="content_editor" class="ace-editor"></div>
		</div>
	</div>
	<div class="buttonbar">
		<a href="./manage/form/mail" class="btn">'.$iconList.'&nbsp;zur Liste</a>
		<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
	</div>
</form>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.3.3/ace.js"></script>
<script>
jQuery(document).ready(function(){
	var textarea = jQuery("#input_content").hide();
	var editor = ace.edit("content_editor", {
		minLines: 15,
		maxLines: 35,
	});
	editor.getSession().setValue(textarea.val());
	editor.setFontSize(14);
	editor.session.on("change", function(){
		textarea.val(editor.getSession().getValue());
	});
});
</script>
<style>
.ace-editor {
	border: 1px solid rgba(127, 127, 127, 0.5);
	border-radius: 6px;
	padding: 6px;
	}
</style>
';

