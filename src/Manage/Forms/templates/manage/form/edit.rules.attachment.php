<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as BootstrapModalTrigger;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var object $form */
/** @var array<object{fileName: string, filePath: string, mimeType: string|FALSE}> $files */
/** @var array<object> $rulesAttachment */
/** @var array<string,string|HtmlTag> $navButtons */

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconTest	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cogs'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconMail	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-envelope'] );

$listRules	= HtmlTag::create( 'div', 'Keine Regeln vorhanden.', ['class' => 'alert alert-info'] );

if( $rulesAttachment ){
	$listRules	= [];
	foreach( $rulesAttachment as $rule ){
		$list	= [];
		foreach( json_decode( $rule->rules ) as $item ){
			$keyLabel	= HtmlTag::create( 'acronym', $item->keyLabel, ['title' => 'Interner Schlüssel: '.$item->key] );
			$valueLabel	= HtmlTag::create( 'acronym', $item->valueLabel, ['title' => 'Interner Schlüssel: '.$item->value] );
			$list[]	= HtmlTag::create( 'li', $keyLabel.' => '.$valueLabel );
		}
		$list	= HtmlTag::create( 'ul', $list, ['style' => 'margin-bottom: 0'] );

		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
			'href'	=> './manage/form/removeRule/'.$form->formId.'/'.$rule->formRuleId,
			'class'	=> 'btn btn-danger btn-small',
		] );
		$listRules[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $list ),
			HtmlTag::create( 'td', $rule->filePath ),
			HtmlTag::create( 'td', $buttonRemove ),
		] );
	}
	$colgroup	= HtmlElements::ColumnGroup( ['', '30%', '60px'] );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['Regeln', 'Datei'] ) );
	$tbody		= HtmlTag::create( 'tbody', $listRules );
	$listRules	= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-striped'] );
}

$optFile	= [];
foreach( $files as $file )
	$optFile[$file->filePath]	= $file->filePath;
$optFile	= HtmlElements::Options( $optFile );

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
$modalTrigger->setAttributes( ['class' => 'btn btn-primary'] );

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
