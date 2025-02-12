<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var array<object> $reasons */

$iconCancel	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'icon-check icon-white'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zurück', [
	'href'	=> './manage/ip/lock',
	'class'	=> 'btn btn-small',
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.' speichern', [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-success',
] );

$optReason	= [];
foreach( $reasons as $reason )
	$optReason[$reason->ipLockReasonId]	= $reason->title;
$optReason	= HtmlElements::Options( $optReason );

$reasonMap	= [];
foreach( $reasons as $reason )
	$reasonMap[$reason->ipLockReasonId]	= $reason;

$tabs   = View_Manage_IP_Lock::renderTabs( $env );

return $tabs.HTML::DivClass( 'row-fluid', '
<script>
let ipLockReasons = '.json_encode( $reasonMap).';

function onUpdateReasonDisplay(event){
	let id = parseInt($(this).val(), 10);
	$("#input_duration").val(ipLockReasons[id].duration);
}
$(document).ready(function(){
	$("#input_reasonId").on("change", onUpdateReasonDisplay).trigger("change");
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
