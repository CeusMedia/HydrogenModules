<?php

$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-check icon-white' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array(
	'href'	=> './manage/ip/lock/reason',
	'class'	=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-success',
) );
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.' entfernen', array(
	'href'	=> './manage/ip/lock/reason/remove/'.$reason->ipLockReasonId,
	'class'	=> 'btn btn-danger btn-small',
) );

$optStatus	= UI_HTML_Elements::Options( array(
	1		=> 'aktiv',
	0		=> 'inaktiv',
), $reason->status );

$tabs   = View_Manage_Ip_Lock::renderTabs( $env, 'reason' );

return $tabs.HTML::DivClass( 'row-fluid', '
<h2><span class="muted">IP-Lock-Gründe:</span> '.$reason->title.'</h2>
<form action="./manage/ip/lock/reason/edit/'.$reason->ipLockReasonId.'" method="post">
	<div class="row-fluid">
		<div class="span6">
			<label for="input_title" class="required mandatory">Titel</label>
			<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $reason->title, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span2">
			<label for="input_code"><abbr title="HTTP-Status-Code">Code</abbr></label>
			<input type="text" name="code" id="input_code" class="span12" required="required" value="'.htmlentities( $reason->code, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span2">
			<label for="input_duration">Dauer <small class="muted">(in Sekunden)</small></label>
			<input type="text" name="duration" id="input_duration" class="span12" value="'.$reason->duration.'"/>
		</div>
		<div class="span2">
			<label for="input_status">Status</label>
			<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_description">Beschreibung</label>
			<textarea name="description" id="input_description" class="span12" rows="5">'.htmlentities( $reason->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
		</div>
	</div>
	<div class="buttonbar">
		'.$buttonCancel.'
		'.$buttonSave.'
		'.$buttonRemove.'
	</div>
</form>
' );
