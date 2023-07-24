<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as BootstrapModalTrigger;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as Html;

$iconAdd	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconEdit	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconSave	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconTest	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-cogs'] );
$iconRemove	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconMail	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-envelope'] );

$listRules	= Html::create( 'div', 'Keine Regeln vorhanden.', ['class' => 'alert alert-info'] );
if( $transferRules ){
	$listRules	= [];
	$modals		= [];
	foreach( $transferRules as $rule ){
		$ruleId		= $rule->formTransferRuleId;
		$target		= $transferTargets[$rule->formTransferTargetId];

		$buttonTest	= Html::create( 'button', $iconTest, [
			'type'	=> 'button',
			'id'	=> 'button-test-'.$ruleId,
			'class'	=> 'btn not-btn-info not-btn-small btn-mini button-test-rules',
		], ['rule-id' => $ruleId] );

		$optTransferTarget	= [];
		foreach( $transferTargets as $item )
			$optTransferTarget[$item->formTransferTargetId]	= $item->title;
		$optTransferTarget	= HtmlElements::Options( $optTransferTarget, $rule->formTransferTargetId );

		$modal	= new BootstrapModalDialog( 'rule-transfer-edit-'.$ruleId );
		$modal->setHeading( 'Transfer-Regel: '.$rule->title );
		$modal->setFormAction( './manage/form/editTransferRule/'.$form->formId.'/'.$ruleId );
		$modal->setSubmitButtonLabel( 'speichern' );
		$modal->setSubmitButtonClass( 'btn btn-primary' );
		$modal->setCloseButtonLabel( 'abbrechen' );
		$modal->setBody( '
		<div class="row-fluid">
			<div class="span8">
				<label for="input_title-'.$ruleId.'" class="mandatory required">Bezeichnung des Transfers</label>
				<input type="text" name="title" id="input_title-'.$ruleId.'" class="span12" required="required" value="'.htmlentities( $rule->title, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span4">
				<label for="input_formTransferTargetId-'.$ruleId.'" class="mandatory required">Transfer-Ziel</label>
				<select name="formTransferTargetId" id="input_formTransferTargetId-'.$ruleId.'" class="span12" required="required">'.$optTransferTarget.'</select>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<label for="input_rules-'.$ruleId.'">Regeln <small class="muted">(im JSON-Format) '.$buttonTest.'</small></label>
				<textarea name="rules" id="input_rules-'.$ruleId.'" class="span12 ace-auto" rows="18" data-ace-option-max-lines="25" data-ace-option-line-height="1" data-ace-flag-font-size="12">'.htmlentities( @$rule->rules, ENT_QUOTES, 'UTF-8' ).'</textarea>
			</div>
		</div>
		' );
		$modalTrigger	= new BootstrapModalTrigger( 'rule-transfer-edit-'.$ruleId.'-trigger' );
		$modalTrigger->setModalId( 'rule-transfer-edit-'.$ruleId )->setLabel( $iconEdit.'&nbsp;bearbeiten' );
		$modalTrigger->setAttributes( ['class' => 'btn not-btn-primary btn-small'] );


		$buttonRemove	= Html::create( 'a', $iconRemove.'&nbsp;entfernen', [
			'href'	=> './manage/form/removeTransferRule/'.$form->formId.'/'.$rule->formTransferRuleId,
			'class'	=> 'btn btn-inverse btn-small',
		] );
		$buttons		= Html::create( 'div', [$modalTrigger, $buttonRemove], ['class' => 'btn-group'] );
		$listRules[]	= Html::create( 'tr', array(
			Html::create( 'td', $rule->title ),
			Html::create( 'td', $target->title ),
			Html::create( 'td', $buttons ),
		) );
		$modals[]		= $modal;
	}
	$colgroup	= HtmlElements::ColumnGroup( ['', '25%', '20%'] );
	$thead		= Html::create( 'thead', HtmlElements::TableHeads( ['Bezeichnung', 'Transfer-Ziel'] ) );
	$tbody		= Html::create( 'tbody', $listRules );
	$listRules	= Html::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-striped'] ).join( $modals );
}

$optTransferTarget	= [];
foreach( $transferTargets as $item )
	$optTransferTarget[$item->formTransferTargetId]	= $item->title;
$optTransferTarget	= HtmlElements::Options( $optTransferTarget );

$modal	= new BootstrapModalDialog( 'rule-transfer-add' );
$modal->setHeading( 'Neue Transfer-Regel' );
$modal->setFormAction( './manage/form/addTransferRule/'.$form->formId );
$modal->setSubmitButtonLabel( 'speichern' );
$modal->setSubmitButtonClass( 'btn btn-primary' );
$modal->setCloseButtonLabel( 'abbrechen' );
$modal->setBody( '
<div class="row-fluid">
	<div class="span8">
		<label for="input_title" class="mandatory required">Bezeichnung des Transfers</label>
		<input type="text" name="title" id="input_title" class="span12" required="required"></input>
	</div>
	<div class="span4">
		<label for="input_formTransferTargetId" class="mandatory required">Transfer-Ziel</label>
		<select name="formTransferTargetId" id="input_formTransferTargetId" class="span12" required="required">'.$optTransferTarget.'</select>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<label for="input_rules">Regeln <small class="muted">(im JSON-Format)</small></label>
		<textarea name="rules" id="input_rules" class="span12 ace-auto" rows="18"></textarea>
	</div>
</div>
' );
$modalTrigger	= new BootstrapModalTrigger();
$modalTrigger->setId( 'rule-transfer-add-trigger' );
$modalTrigger->setModalId( 'rule-transfer-add' )->setLabel( $iconAdd.'&nbsp;neuer Transfer' );
$modalTrigger->setAttributes( ['class' => 'btn btn-primary'] );

$script		= '
<script>
var FormsTransferRuleTest = {
	init: function(){
		jQuery(".button-test-rules").bind("click", function(){
			var button = jQuery(this);
			var ruleId = button.data("rule-id");
			var modal = jQuery("#rule-transfer-edit-"+ruleId);
			var rules = modal.find("#input_rules-"+ruleId).val();
			FormsTransferRuleTest.updateTransferRulesTestTrigger(ruleId, rules);
		});
	},
	testTransferRules: function(ruleId, rules, callback){
		jQuery.ajax({
			url: "./manage/form/ajaxTestTransferRules",
			method: "POST",
			dataType: "json",
			data: {
				ruleId: ruleId,
				rules: rules
			},
			success: callback
		});
	},
	updateTransferRulesTestTrigger: function(ruleId, rules){
		var callback = function(json){
			var button = jQuery("#button-test-"+ruleId);
			button.prop("title", null);
			button.removeClass("btn-info btn-success btn-danger")
			if(json.status !== "empty"){
				if(json.status === "exception" || json.status === "error"){
					button.addClass("btn-danger");
					button.prop("title", json.message);
				}
				else if(json.status === "success" || json.status === "parsed"){
					button.addClass("btn-success");
				}
			}
			button.blur();
		}
		FormsTransferRuleTest.testTransferRules(ruleId, rules, callback);
	}
}
jQuery(document).ready(function(){
	FormsTransferRuleTest.init();
});
</script>';


$style	= '
<style>
span.indicator-transfer-rules-test {
	display: inline-block;
	width: 24px;
	height: 24px;
	border: 1px solid gray;
	border-radius: 0.3em;
	}
span.indicator-transfer-rules-test.test-success {
	background-color: green;
	}
span.indicator-transfer-rules-test.test-fail {
	background-color: green;
	}
</style>';
return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<div class="content-panel-inner">
				'.$listRules.'
				<div class="buttonbar">
					'.$navButtons['list'].'
					'.$navButtons['prevCustomer'].'
					'.$modalTrigger->render().'
<!--					'.$navButtons['nextFills'].'-->
				</div>
			</div>
		</div>
	</div>
</div>'.$modal->render().$script.$style;
