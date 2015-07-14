<?php

$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-check icon-white' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array(
	'href'	=> './manage/ip/lock/filter',
	'class'	=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );

$optMethod	= UI_HTML_Elements::Options( array(
	'' => 'alle',
	'GET'	=> 'GET',
	'HEAD'	=> 'HEAD',
	'POST'	=> 'POST',
	'PUT'	=> 'PUT'
) );

$optLockStatus	= UI_HTML_Elements::Options( array(
	1		=> 'aktive Sperre',
	0		=> 'nur Sperrantrag',
) );

$optReason	= array();
foreach( $reasons as $reason )
	$optReason[$reason->ipLockReasonId]	= $reason->title;
$optReason	= UI_HTML_Elements::Options( $optReason );

$reasonMap	= array();
foreach( $reasons as $reason )
	$reasonMap[$reason->ipLockReasonId]	= $reason;


$panelAdd	= '
<div class="content-panel">
	<h3><a class="muted" href="./manage/ip/lock/filter">IP-Sperr-Filter:</a> Neu</h2>
	<div class="content-panel-inner">
		<form action="./manage/ip/lock/filter/add" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_title" class="required mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span2">
					<label for="input_method">HTTP-Methode</label>
					<select name="method" id="input_method" class="span12">'.$optMethod.'</select>
				</div>
				<div class="span4">
					<label for="input_pattern" class="required mandatory">URI-Muster</label>
					<input type="text" name="pattern" id="input_pattern" class="span12" required="required"/>
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
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>
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
';

$tabs   = View_Manage_Ip_Lock::renderTabs( $env, 'filter' );
return $tabs.HTML::DivClass( 'row-fluid', HTML::DivClass( 'span12', $panelAdd ) );
