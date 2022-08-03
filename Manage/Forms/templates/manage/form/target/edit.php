<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;zurück', array( 'class' => 'btn btn-small', 'href' => './manage/form/target' ) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;speichern', array( 'type' => 'submit', 'class' => 'btn btn-primary' ) );

$optStatus		= [
	0	=> 'inaktiv',
	1	=> 'aktiv',
];
$optStatus		= UI_HTML_Elements::Options( $optStatus, $target->status );

$table		= '';
if( count( $fails ) ){
	$rows	= [];
	foreach( $fails as $nr => $fail )
	{
		$link	= UI_HTML_Tag::create( 'a', $fail->fillId, ['href' => './manage/form/fill/view/'.$fail->fillId] );
		$rows[]	= UI_HTML_Tag::create( 'tr', [
			UI_HTML_Tag::create( 'td', $nr + 1 ),
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', '<tt>'.$fail->fillTransferMessage.'</tt>' ),
			UI_HTML_Tag::create( 'td', date( 'd.m.Y H:i:s', $fail->createdAt ) ),
			UI_HTML_Tag::create( 'td', date( 'd.m.Y H:i:s', $fail->failedAt ) ),
		] );
	}
	$thead	= UI_HTML_Tag::create( 'tr', [
		UI_HTML_Tag::create( 'th', '#' ),
		UI_HTML_Tag::create( 'th', 'Eintrag' ),
		UI_HTML_Tag::create( 'th', 'Fehlermeldung' ),
		UI_HTML_Tag::create( 'th', 'eingegangen' ),
		UI_HTML_Tag::create( 'th', 'gescheitert' ),
	] );
	$colgroup	= UI_HTML_Elements::ColumnGroup( '40px', '60px', '*', '120px', '120px' );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-fixed table-striped'] );

	$table	= UI_HTML_Tag::create( 'div', [
		UI_HTML_Tag::create( 'h3', 'Gescheiterte Transfers <small class="muted">(der letzten 4 Wochen)</small>' ),
		UI_HTML_Tag::create( 'div', $table, ['class' => 'content-panel-inner'] ),
	], ['class' => 'content-panel'] );

}


return '<div class="content-panel">
	<h3><span class="muted">Transferziel: </span>'.$target->title.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/form/target/edit/'.$target->formTransferTargetId.'" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_title" class="mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $target->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span6">
					<label for="input_className" class="mandatory">Implementierung <small class="muted">(Klassenname)</small></label>
					<input type="text" name="className" id="input_className" class="span12" required="required" value="'.htmlentities( $target->className, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span9">
					<label for="input_baseUrl" class="mandatory">API-URL</label>
					<input type="text" name="baseUrl" id="input_baseUrl" class="span12" required="required" value="'.htmlentities( $target->baseUrl, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_apiKey">API-Schüssel</label>
					<input type="text" name="apiKey" id="input_apiKey" class="span12"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>'.$table;
