<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var Entity_Form_Import_Rule $rule */
/** @var array<object> $connections */
/** @var array<Entity_Form> $forms */
/** @var array<string,string> $folders */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconTest		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cogs'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;zurück', ['class' => 'btn btn-small', 'href' => './manage/form/import'] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', ['type' => 'submit', 'class' => 'btn btn-primary'] );

$buttonTest	= HtmlTag::create( 'button', $iconTest, [
	'type'	=> 'button',
	'id'	=> 'button-test-'.$rule->formImportRuleId,
	'class'	=> 'btn not-btn-info not-btn-small btn-mini button-test-rules',
], ['rule-id' => $rule->formImportRuleId] );


$statuses	= [
	Model_Form_Import_Rule::STATUS_NEW		=> 'neu',
	Model_Form_Import_Rule::STATUS_TEST		=> 'Testmodus',
	Model_Form_Import_Rule::STATUS_ACTIVE	=> 'aktiviert',
	Model_Form_Import_Rule::STATUS_PAUSED	=> 'pausiert',
	Model_Form_Import_Rule::STATUS_DISABLED	=> 'deaktiviert',
];

$optStatus		= HtmlElements::Options( $statuses, $rule->status );

$optConnection	= [];
foreach( $connections as $connection )
	$optConnection[$connection->importConnectionId]	= $connection->title;
$optConnection	= HtmlElements::Options( $optConnection, $rule->importConnectionId );

$optForm	= [];
foreach( $forms as $formId => $form )
	$optForm[$formId]	= $form->title;
$optForm	= HtmlElements::Options( $optForm, $rule->formId );

$optMoveTo	= ['' => ''];
foreach( $folders as $folder )
	$optMoveTo[$folder]	= $folder;
$optMoveTo	= HtmlElements::Options( $optMoveTo, $rule->moveTo );

$form	= '<div class="content-panel" id="rule-import-edit-'.$rule->formImportRuleId.'">
	<h3><span class="muted">Importquelle: </span>'.$rule->title.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/form/import/edit/'.$rule->formImportRuleId.'" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_title" class="mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $rule->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_importConnectionId" class="silent-mandatory">Importquelle</label>
					<select name="importConnectionId" id="input_importConnectionId" class="span12" required="required">'.$optConnection.'</select>
				</div>
				<div class="span6">
					<label for="input_formId" class="silent-mandatory">Formular</label>
					<select name="formId" id="input_formId" class="span12" required="required">'.$optForm.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_rules-'.$rule->formImportRuleId.'">Regeln <small class="muted">(im JSON-Format) '.$buttonTest.'</small></label>
					<textarea name="rules" id="input_rules-'.$rule->formImportRuleId.'" class="span12 ace-auto" rows="18" data-ace-option-max-lines="25" data-ace-option-line-height="1" data-ace-flag-font-size="12">'.htmlentities( $rule->rules ?? '', ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_searchCriteria">Suchkriterien <small class="muted">(im IMAP-Format)</small></label>
					<textarea name="searchCriteria" id="input_searchCriteria" class="span12 ace-auto" rows="18" data-ace-option-max-lines="5" data-ace-option-line-height="1" data-ace-flag-font-size="12">'.htmlentities( $rule->searchCriteria ?? '', ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
				<div class="span6">
					<label for="input_options-'.$rule->formImportRuleId.'">Optionen</label>
					<textarea name="options" id="input_options-'.$rule->formImportRuleId.'" class="span12 ace-auto" rows="18" data-ace-option-max-lines="5" data-ace-option-line-height="1" data-ace-flag-font-size="12">'.htmlentities( $rule->options ?? '', ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_renameTo">anschließend verschieben nach</label>
<!--					<input type="text" name="moveTo" id="input_moveTo" class="span12" value="'.htmlentities( $rule->moveTo ?? '', ENT_QUOTES, 'UTF-8' ).'"/>-->
					<select name="moveTo" id="input_moveTo" class="span12">'.$optMoveTo.'</select>
				</div>
				<div class="span4">
					<label for="input_renameTo"><strike class="muted">anschließend umbenennen zu</strike></label>
					<input type="text" name="renameTo" id="input_renameTo" class="span12" disabled="disabled" value="'.htmlentities( $rule->renameTo ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>';

$js	= $env->getPage()->js->addScriptOnReady('FormsImportRuleTest.init();');
return $form;
