<?php

$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-check icon-white' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array(
	'href'	=> './manage/ip/lock/reason',
	'class'	=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-success',
) );

$optStatus	= UI_HTML_Elements::Options( array(
	1		=> 'aktiv',
	0		=> 'inaktiv',
) );

$tabs   = View_Manage_Ip_Lock::renderTabs( $env, 'reason' );

return $tabs.HTML::DivClass( 'row-fluid', '
<h2><span class="muted">IP-Lock-Gründe:</span> Neu</h2>
<form action="./manage/ip/lock/reason/add" method="post">
	<div class="row-fluid">
		<div class="span6">
			<label for="input_title" class="required mandatory">Titel</label>
			<input type="text" name="title" id="input_title" class="span12" required="required"/>
		</div>
		<div class="span2">
			<label for="input_code"><abbr title="HTTP-Status-Code">Code</abbr></label>
			<input type="text" name="code" id="input_code" class="span12" required="required"/>
		</div>
		<div class="span2">
			<label for="input_duration">Dauer <small class="muted">(in Sekunden)</small></label>
			<input type="text" name="pattern" id="input_duration" class="span12"/>
		</div>
		<div class="span2">
			<label for="input_status">Zustand</label>
			<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_description">Beschreibung</label>
			<textarea name="description" class="span12" rows="5"></textarea>
		</div>
	</div>
	<div class="buttonbar">
		'.$buttonCancel.'
		'.$buttonSave.'
	</div>
</form>
' );
