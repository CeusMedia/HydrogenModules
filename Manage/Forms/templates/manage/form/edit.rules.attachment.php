<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as BootstrapModalTrigger;
use UI_HTML_Tag as Html;

$iconAdd	= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconEdit	= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
$iconSave	= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconTest	= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-cogs' ) );
$iconRemove	= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconMail	= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-envelope' ) );

$listRules	= Html::create( 'div', 'Keine Regeln vorhanden.', array( 'class' => 'alert alert-info' ) );

if( $rulesAttachment ){
	$listRules	= array();
	foreach( $rulesAttachment as $rule ){
		$list	= array();
		foreach( json_decode( $rule->rules ) as $item ){
			$keyLabel	= Html::create( 'acronym', $item->keyLabel, array( 'title' => 'Interner Schlüssel: '.$item->key ) );
			$valueLabel	= Html::create( 'acronym', $item->valueLabel, array( 'title' => 'Interner Schlüssel: '.$item->value ) );
			$list[]	= Html::create( 'li', $keyLabel.' => '.$valueLabel );
		}
		$list	= Html::create( 'ul', $list, array( 'style' => 'margin-bottom: 0' ) );

		$file	= $rule->filePath;

		$buttonRemove	= Html::create( 'a', $iconRemove, array(
			'href'	=> './manage/form/removeRule/'.$form->formId.'/'.$rule->formRuleId,
			'class'	=> 'btn btn-danger btn-small',
		) );
		$listRules[]	= Html::create( 'tr', array(
			Html::create( 'td', $list ),
			Html::create( 'td', $file ),
			Html::create( 'td', $buttonRemove ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array( '', '30%', '60px' ) );
	$thead		= Html::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Regeln', 'Datei' ) ) );
	$tbody		= Html::create( 'tbody', $listRules );
	$listRules	= Html::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-striped' ) );
}

$optFile	= [];
foreach( $files as $file )
	$optFile[$file->filePath]	= $file->filePath;
$optFile	= UI_HTML_Elements::Options( $optFile );

$modal	= new BootstrapModalDialog( 'rule-attachment-add' );
$modal->setHeading( 'Neuer Anhang' );
$modal->setFormAction( './manage/form/addRule/'.$form->formId.'/'.Model_Form_Rule::TYPE_ATTACHMENT );
$modal->setSubmitButtonLabel( 'speichern' );
$modal->setSubmitButtonClass( 'btn btn-primary' );
$modal->setCloseButtonLabel( 'abbrechen' );
$modal->setBody( '
<div class="row-fluid">
	<div class="span6">
		<label for="input_attachment_ruleKey_0" class="mandatory required">Feld</label>
		<select name="ruleKey_0" id="input_attachment_ruleKey_0" class="span12" required="required"><option value="">- bitte wählen -</option></select>
	</div>
	<div class="span6">
		<label for="input_attachment_ruleValue_0">Wert</label>
		<select name="ruleValue_0" id="input_attachment_ruleValue_0" class="span12"></select>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<label for="input_attachment_ruleKey_1">Feld</label>
		<select name="ruleKey_1" id="input_attachment_ruleKey_1" class="span12"><option value="">- bitte wählen -</option></select>
	</div>
	<div class="span6">
		<label for="input_attachment_ruleValue_1">Wert</label>
		<select name="ruleValue_1" id="input_attachment_ruleValue_1" class="span12"></select>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<label for="input_attachment_ruleKey_2">Feld</label>
		<select name="ruleKey_2" id="input_attachment_ruleKey_2" class="span12"><option value="">- bitte wählen -</option></select>
	</div>
	<div class="span6">
		<label for="input_attachment_ruleValue_2">Wert</label>
		<select name="ruleValue_2" id="input_attachment_ruleValue_2" class="span12"></select>
	</div>
</div>
<div class="row-fluid">
	<div class="span8">
		<label for="input_attachment_file" class="mandatory required">Datei</label>
		<select name="filePath" id="input_attachment_file" class="span12" required="required">'.$optFile.'</select>
	</div>
</div>
<input type="hidden" name="ruleKeyLabel_0" id="input_attachment_ruleKeyLabel_0"/>
<input type="hidden" name="ruleKeyLabel_1" id="input_attachment_ruleKeyLabel_1"/>
<input type="hidden" name="ruleKeyLabel_2" id="input_attachment_ruleKeyLabel_2"/>
<input type="hidden" name="ruleValueLabel_0" id="input_attachment_ruleValueLabel_0"/>
<input type="hidden" name="ruleValueLabel_1" id="input_attachment_ruleValueLabel_1"/>
<input type="hidden" name="ruleValueLabel_2" id="input_attachment_ruleValueLabel_2"/>
' );
$modalTrigger	= new BootstrapModalTrigger();
$modalTrigger->setId( 'rule-attachment-add-trigger' );
$modalTrigger->setModalId( 'rule-attachment-add' )->setLabel( $iconAdd.'&nbsp;neuer Anhang' );
$modalTrigger->setAttributes( array( 'class' => 'btn btn-primary' ) );

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
</div>'.$modal->render();
