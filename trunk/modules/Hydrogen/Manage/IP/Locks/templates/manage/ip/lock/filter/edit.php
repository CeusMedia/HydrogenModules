<?php

$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-check icon-white' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array(
	'href'	=> './manage/ip/lock/filter',
	'class'	=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-success',
) );
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.' entfernen', array(
	'href'	=> './manage/ip/lock/filter/remove/'.$filter->ipLockFilterId,
	'class'	=> 'btn btn-danger btn-small',
) );

$optMethod	= UI_HTML_Elements::Options( array(
	'' => 'alle',
	'GET'	=> 'GET',
	'HEAD'	=> 'HEAD',
	'POST'	=> 'POST',
	'PUT'	=> 'PUT'
), $filter->method );

$optLockStatus	= UI_HTML_Elements::Options( array(
	1		=> 'aktive Sperre',
	0		=> 'nur Sperrantrag',
), $filter->lockStatus );

$optStatus	= UI_HTML_Elements::Options( array(
	1		=> 'aktiv',
	0		=> 'inaktiv',
), $filter->status );

$optReason	= array();
foreach( $reasons as $reason )
	$optReason[$reason->ipLockReasonId]	= $reason->title;
$optReason	= UI_HTML_Elements::Options( $optReason, $filter->reasonId );

$reasonMap	= array();
foreach( $reasons as $reason )
	$reasonMap[$reason->ipLockReasonId]	= $reason;

$tabs   = View_Manage_Ip_Lock::renderTabs( $env, 'filter' );

return $tabs.HTML::DivClass( 'row-fluid', '
<script>
var ipLockReasons = '.json_encode( $reasonMap).';

function onUpdateReasonDisplay(event){
	var id = parseInt($(this).val(), 10);
	$("#input_duration").val(ipLockReasons[id].duration);
}
$(document).ready(function(){
	$("#input_reasonId").bind("change", onUpdateReasonDisplay).trigger("change");
});
</script>

<h2><span class="muted">IP-Lock-Filter:</span> '.$filter->title.'</h2>
<form action="./manage/ip/lock/filter/edit/'.$filter->ipLockFilterId.'" method="post">
	<div class="row-fluid">
		<div class="span6">
			<label for="input_title" class="required mandatory">Titel</label>
			<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $filter->title, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span2">
			<label for="input_method">HTTP-Methode</label>
			<select name="method" id="input_method" class="span12">'.$optMethod.'</select>
		</div>
		<div class="span4">
			<label for="input_pattern" class="required mandatory">URI-Muster</label>
			<input type="text" name="pattern" id="input_pattern" class="span12" required="required" value="'.htmlentities( $filter->pattern, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span4">
			<label for="input_reasonId">Grund</label>
			<select name="reasonId" id="input_reasonId" class="span12">'.$optReason.'</select>
		</div>
		<div class="span2">
			<label for="input_duration">Dauer <small class="muted">(in Sekunden)</small></label>
			<input type="text" name="duration" id="input_duration" class="span12" readonly="readonly" disabled="disabled"/>
		</div>
		<div class="span2">
			<label for="input_lockStatus">Sperrstatus</label>
			<select name="lockStatus" id="input_lockStatus" class="span12">'.$optLockStatus.'</select>
		</div>
		<div class="span2">
			<label for="input_status">Filterstatus</label>
			<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
		</div>
	</div>
	<div class="buttonbar">
		'.$buttonCancel.'
		'.$buttonSave.'
		'.$buttonRemove.'
	</div>
</form>
' );
