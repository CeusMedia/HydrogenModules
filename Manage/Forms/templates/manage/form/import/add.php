<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;zurück', array( 'class' => 'btn btn-small', 'href' => './manage/form/import' ) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;speichern', array( 'type' => 'submit', 'class' => 'btn btn-primary' ) );

$statuses	= [
	Model_Form_Import_Rule::STATUS_NEW		=> 'neu',
	Model_Form_Import_Rule::STATUS_TEST		=> 'Testmodus',
];

$optStatus		= UI_HTML_Elements::Options( $statuses );

$optConnection	= [];
foreach( $connections as $connection )
	$optConnection[$connection->importConnectionId]	= $connection->title;
$optConnection	= UI_HTML_Elements::Options( $optConnection );

$optForm	= [];
foreach( $forms as $formId => $form )
	$optForm[$formId]	= $form->title;
$optForm	= UI_HTML_Elements::Options( $optForm );

return '<div class="content-panel" id="rule-import-add">
	<h3>Neue Importquelle</h3>
	<div class="content-panel-inner">
		<form action="./manage/form/import/add" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_title" class="mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span2">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_importConnectionId" class="silent-mandatory">Importquelle</label>
					<select name="importConnectionId" id="input_importConnectionId" class="span12" required="required">'.$optConnection.'</select>
				</div>
				<div class="span6">
					<label for="input_formId" class="silent-mandatory">Formular</label>
					<select name="formId" id="input_formId" class="span12" required="required">'.$optForm.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_rules">Regeln <small class="muted">(im JSON-Format)</small></label>
					<textarea name="rules" id="input_rules" class="span12 ace-auto" rows="18" data-ace-option-max-lines="25" data-ace-option-line-height="1" data-ace-flag-font-size="12"></textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_searchCriteria">Suchkriterien <small class="muted">(im IMAP-Format)</small></label>
					<textarea name="searchCriteria" id="input_searchCriteria" class="span12 ace-auto" rows="18" data-ace-option-max-lines="5" data-ace-option-line-height="1" data-ace-flag-font-size="12">SUBJECT "xyz"</textarea>
				</div>
				<div class="span6">
					<label for="input_options">Optionen <small class="muted">(im JSON-Format)</small></label>
					<textarea name="rules" id="input_options" class="span12 ace-auto" rows="18" data-ace-option-max-lines="5" data-ace-option-line-height="1" data-ace-flag-font-size="12">{}</textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_renameTo">anschließend verschieben nach</label>
					<input type="text" name="moveTo" id="input_moveTo" class="span12"/>
				</div>
				<div class="span4">
					<label for="input_renameTo"><strike class="muted">anschließend umbenennen zu</strike></label>
					<input type="text" name="renameTo" id="input_renameTo" class="span12" disabled="disabled"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>';
