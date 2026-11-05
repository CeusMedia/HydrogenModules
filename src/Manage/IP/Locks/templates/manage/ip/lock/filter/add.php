<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var array<Entity_IP_Lock_Reason> $reasons */
/** @var bool $canAdd */
/** @var bool $canEdit */
/** @var bool $canActivate */
/** @var bool $canDeactivate */
/** @var bool $canRemove */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['add'];

$iconCancel	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'icon-check icon-white'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' '.$w->buttonCancel, [
	'href'	=> './manage/ip/lock/filter',
	'class'	=> 'btn btn-small',
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.' '.$w->buttonSave, [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
] );

$optMethod	= HtmlElements::Options( [
	'' => $w->optionMethodAll,
	'GET'	=> 'GET',
	'HEAD'	=> 'HEAD',
	'POST'	=> 'POST',
	'PUT'	=> 'PUT'
] );

$optLockStatus	= HtmlElements::Options( [
	Model_IP_Lock_Filter::LOCK_STATUS_IMMEDIATE	=> $words['lockStatuses'][Model_IP_Lock_Filter::LOCK_STATUS_IMMEDIATE],
	Model_IP_Lock_Filter::LOCK_STATUS_REQUEST	=> $words['lockStatuses'][Model_IP_Lock_Filter::LOCK_STATUS_REQUEST],
], Model_IP_Lock_Filter::LOCK_STATUS_IMMEDIATE );

$optReason	= [];
foreach( $reasons as $reason )
	$optReason[$reason->ipLockReasonId]	= $reason->title;
$optReason	= HtmlElements::Options( $optReason );

$reasonMap	= [];
foreach( $reasons as $reason )
	$reasonMap[$reason->ipLockReasonId]	= $reason;

$panelAdd	= '
<div class="content-panel">
	<h3><a class="muted" href="./manage/ip/lock/filter">'.$w->heading.'</a> '.$w->headingNew.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/ip/lock/filter/add" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_title" class="required mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span2">
					<label for="input_method">'.$w->labelMethod.'</label>
					<select name="method" id="input_method" class="span12">'.$optMethod.'</select>
				</div>
				<div class="span4">
					<label for="input_pattern" class="required mandatory">'.$w->labelPattern.'</label>
					<input type="text" name="pattern" id="input_pattern" class="span12" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_reasonId">'.$w->labelReason.'</label>
					<select name="reasonId" id="input_reasonId" class="span12">'.$optReason.'</select>
				</div>
				<div class="span2">
					<label for="input_duration">'.$w->labelDuration.' <small class="muted">'.$w->labelDuration_suffix.'</small></label>
					<input type="text" name="duration" id="input_duration" class="span12" readonly="readonly" disabled="disabled"/>
				</div>
				<div class="span2">
					<label for="input_lockStatus">'.$w->labelLockStatus.'</label>
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
let ipLockReasons = '.json_encode( $reasonMap).';

function onUpdateReasonDisplay(event){
	let id = parseInt($(this).val(), 10);
	$("#input_duration").val(ipLockReasons[id].duration);
}

$(document).ready(function(){
	$("#input_reasonId").on("change", onUpdateReasonDisplay).trigger("change");
});
</script>
';

$tabs   = View_Manage_IP_Lock::renderTabs( $env, 'filter' );
return $tabs.HTML::DivClass( 'row-fluid', HTML::DivClass( 'span12', $panelAdd ) );
