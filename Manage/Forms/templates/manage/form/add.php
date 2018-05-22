<?php

$modelForm	= new Model_Form( $this->env );
$modelBlock	= new Model_Block( $this->env );

$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$statuses	= array(
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
);
$optStatus	= UI_HTML_Elements::Options( $statuses );

$types		= array(
	0		=> 'direkter Versand',
	1		=> 'mit Double-Opt-In',
);
$optType	= UI_HTML_Elements::Options( $types );

$optDelivery	= array( '' => '- keine -' );
foreach( $this->modelBlock->getAll( array( 'identifier' => 'email_customer_result_%' ) ) as $item )
	$optDelivery[$item->identifier] = $item->title;
		$optDelivery	= UI_HTML_Elements::Options( $optDelivery );

return '
<h2><span class="muted">Formular:</span> Neu</h2>
<form action="./manage/form/add" method="post">
	<div class="row-fluid">
		<div class="span1">
			<label for="input_formId">ID</label>
			<input type="text" name="formId" id="input_formId" class="span12" disabled="disabled"/>
		</div>
		<div class="span7">
			<label for="input_title">Titel</label>
			<input type="text" name="title" id="input_title" class="span12"/>
		</div>
		<div class="span2">
			<label for="input_type">Typ</label>
			<select name="type" id="input_type" class="span12">'.$optType.'</select>
		</div>
		<div class="span2">
			<label for="input_status">Zustand</label>
			<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span8">
			<label for="input_receivers">Empfänger <small class="muted">(mit Komma getrennt)</small></label>
			<input type="text" name="receivers" id="input_receivers" class="span12"/>
		</div>
		<div class="span4">
			<label for="input_delivery">Ergebnis-Email an Kunden</label>
			<select name="delivery" id="input_delivery" class="span12">'.$optDelivery.'</select>
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
		<a href="./manage/form" class="btn">'.$iconList.'&nbsp;zur Liste</a>
		<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
	</div>
</form>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.3.3/ace.js"></script>
<script>
jQuery(document).ready(function(){
	var textarea = jQuery("#input_content").hide();
	var editor = ace.edit("content_editor", {
		minLines: 20,
		maxLines: 50,
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
	}
}

