<?php

$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-check icon-white' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array(
	'href'	=> './manage/ip/lock',
	'class'	=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-success',
) );

$optReason	= array();
foreach( $reasons as $reason )
	$optReason[$reason->ipLockReasonId]	= $reason->title;
$optReason	= UI_HTML_Elements::Options( $optReason );

$reasonMap	= array();
foreach( $reasons as $reason )
	$reasonMap[$reason->ipLockReasonId]	= $reason;

$tabs   = View_Manage_Ip_Lock::renderTabs( $env );

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

<h2><span class="muted">IP-Locks:</span> Neu</h2>
<form action="./manage/ip/lock/add" method="post">
	<div class="row-fluid">
		<div class="span2">
			<label for="input_ip" class="required mandatory">IP-Adresse</label>
			<input type="text" name="ip" id="input_id" class="span12" required="required"/>
		</div>
		<div class="span4">
			<label for="input_reasonId">Grund</label>
			<select name="reasonId" id="input_reasonId" class="span12">'.$optReason.'</select>
		</div>
		<div class="span2">
			<label for="input_duration">Dauer <small class="muted">(in Sekunden)</small></label>
			<input type="text" name="duration" id="input_duration" class="span12" readonly="readonly" disabled="disabled"/>
		</div>
	</div>
	<div class="buttonbar">
		'.$buttonCancel.'
		'.$buttonSave.'
	</div>
</form>
' );
