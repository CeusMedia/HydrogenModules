<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;zurück', ['class' => 'btn btn-small', 'href' => './manage/form/target'] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', ['type' => 'submit', 'class' => 'btn btn-primary'] );

$optStatus		= [
	0	=> 'inaktiv',
	1	=> 'aktiv',
];
$optStatus		= HtmlElements::Options( $optStatus );

return '<div class="content-panel">
	<h3>Neues Transferziel</h3>
	<div class="content-panel-inner">
		<form action="./manage/form/target/add" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_title" class="mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span6">
					<label for="input_className" class="mandatory">Implementierung <small class="muted">(Klassenname)</small></label>
					<input type="text" name="className" id="input_className" class="span12" required="required"/>
				</div>
				<div class="span2">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span9">
					<label for="input_baseUrl" class="mandatory">API-URL</label>
					<input type="text" name="baseUrl" id="input_baseUrl" class="span12" required="required"/>
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
</div>';
