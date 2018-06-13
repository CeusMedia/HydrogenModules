<?php

$modelBlock	= new Model_Form_Block( $env );

$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconBlock	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-square' ) );

$statuses	= array(
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
);
$optStatus	= UI_HTML_Elements::Options( $statuses, $form->status );

$types		= array(
	0		=> 'direkter Versand',
	1		=> 'mit Double-Opt-In',
);
$optType	= UI_HTML_Elements::Options( $types, $form->type );

$withinBlocks	= array();

$listBlocksWithin	= UI_HTML_Tag::create( 'p', '<em class="muted">Keine.</em>' );
$matches		= array();
preg_match_all( '/\[block_(\S+)\]/', $form->content, $matches );
if( isset( $matches[0] ) && count( $matches[0] ) ){
	$list	= array();
	foreach( array_keys( $matches[0] ) as $nr ){
		$item	= $modelBlock->getByIndex( 'identifier', $matches[1][$nr] );
		if( !$item )
			continue;
		$link	= UI_HTML_Tag::create( 'a', $iconBlock.'&nbsp;'.$item->title, array(
			'href'	=> './manage/form/block/edit/'.$item->blockId,
		) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	if( $list )
		$listBlocksWithin	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'unstyled' ) );
}

$optMail	= array( '' => '- keine -' );
foreach( $mails as $item )
	$optMail[$item->mailId]	= $item->title;
$optMail	= UI_HTML_Elements::Options( $optMail, $form->mailId );


$listRules	= array();
foreach( $rules as $rule ){
	foreach( $mails as $item )
		if( $item->mailId == $rule->mailId )
			$mail	= $item->title;

	$list	= array();
	foreach( json_decode( $rule->rules ) as $item )
		$list[]	= UI_HTML_Tag::create( 'li', $item->keyLabel.' => '.$item->valueLabel );
	$list	= UI_HTML_Tag::create( 'ul', $list, array( 'style' => 'margin-bottom: 0' ) );

	$addresses		= join( '<br/>', preg_split( '/\s*,\s*/', $rule->mailAddresses ) );
	$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
		'href'	=> './manage/form/removeRule/'.$form->formId.'/'.$rule->formRuleId,
		'class'	=> 'btn btn-danger btn-small',
	) );
	$listRules[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $list ),
		UI_HTML_Tag::create( 'td', $mail ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $addresses ) ),
		UI_HTML_Tag::create( 'td', $buttonRemove ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( array( '', '25%', '20%', '60px' ) );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Regeln', 'E-Mail', 'Empfänger' ) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $listRules );
$listRules	= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-striped' ) );

$modal	= new \CeusMedia\Bootstrap\Modal( 'rule-add' );
$modal->setHeading( 'Neue Regel' );
$modal->setFormAction( './manage/form/addRule/'.$form->formId );
$modal->setSubmitButtonLabel( 'speichern' );
$modal->setSubmitButtonClass( 'btn btn-primary' );
$modal->setCloseButtonLabel( 'abbrechen' );
$modal->setBody( '
<div class="row-fluid">
	<div class="span6">
		<label for="input_ruleKey_0" class="mandatory required">Feld</label>
		<select name="ruleKey_0" id="input_ruleKey_0" class="span12" required="required"><option value="">- bitte wählen -</option></select>
	</div>
	<div class="span6">
		<label for="input_ruleValue_0">Wert</label>
		<select name="ruleValue_0" id="input_ruleValue_0" class="span12"></select>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<label for="input_ruleKey_1">Feld</label>
		<select name="ruleKey_1" id="input_ruleKey_1" class="span12"><option value="">- bitte wählen -</option></select>
	</div>
	<div class="span6">
		<label for="input_ruleValue_1">Wert</label>
		<select name="ruleValue_1" id="input_ruleValue_1" class="span12"></select>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<label for="input_ruleKey_2">Feld</label>
		<select name="ruleKey_2" id="input_ruleKey_2" class="span12"><option value="">- bitte wählen -</option></select>
	</div>
	<div class="span6">
		<label for="input_ruleValue_2">Wert</label>
		<select name="ruleValue_2" id="input_ruleValue_2" class="span12"></select>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<label for="input_mailAddresses" class="mandatory required">E-Mail-Adressen <small class="muted">(getrennt mit Komma)</small></label>
		<input type="text" name="mailAddresses" id="input_mailAddresses" class="span12" required="required"/>
	</div>
</div>
<div class="row-fluid">
	<div class="span8">
		<label for="input_mailId" class="mandatory required">E-Mail</label>
		<select name="mailId" id="input_mailId" class="span12" required="required">'.$optMail.'</select>
	</div>
</div>
<input type="hidden" name="ruleKeyLabel_0" id="input_ruleKeyLabel_0"/>
<input type="hidden" name="ruleKeyLabel_1" id="input_ruleKeyLabel_1"/>
<input type="hidden" name="ruleKeyLabel_2" id="input_ruleKeyLabel_2"/>
<input type="hidden" name="ruleValueLabel_0" id="input_ruleValueLabel_0"/>
<input type="hidden" name="ruleValueLabel_1" id="input_ruleValueLabel_1"/>
<input type="hidden" name="ruleValueLabel_2" id="input_ruleValueLabel_2"/>
' );
$modalTrigger	= new \CeusMedia\Bootstrap\Modal\Trigger();
$modalTrigger->setModalId( 'rule-add' )->setLabel( $iconAdd.'&nbsp;neue Regel' );
$modalTrigger->setAttributes( array( 'class' => 'btn btn-primary' ) );

return '
<div id="shadow-form" style="display: none"></div>
<script>
jQuery(document).ready(function(){
	RuleManager.init('.$form->formId.');
	RuleManager.loadFormView();
});
</script>
<h2><a href="./manage/form" class="muted">Formular:</a> '.$form->title.'</h2>
<form action="./manage/form/edit/'.$form->formId.'" method="post">
	<div class="row-fluid">
		<div class="span1">
			<label for="input_formId">ID</label>
			<input type="text" name="formId" id="input_formId" class="span12" disabled="disabled" value="'.htmlentities( $form->formId, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span7">
			<label for="input_title">Titel</label>
			<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $form->title, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span2">
			<label for="input_type">Typ</label>
			<select name="type" id="input_type" class="span12">'.$optType.'</select>
		</div>
		<div class="span2">
			<label for="input_status">Zustand</label>
			<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span8">
			<label for="input_receivers">Empfänger <small class="muted">(mit Komma getrennt)</small></label>
			<input type="text" name="receivers" id="input_receivers" class="span12" value="'.htmlentities( $form->receivers, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span4">
			<label for="input_mailId">Ergebnis-Email an Kunden</label>
			<select name="mailId" id="input_mailId" class="span12">'.$optMail.'</select>
		</div>
	</div>
	<div class="row-fluid" style="margin-bottom: 1em">
		<div class="span12">
			<label for="input_content">Inhalt</label>
			<textarea name="content" id="input_content" class="span12" rows="20">'.htmlentities( $form->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
			<div id="content_editor" class="ace-editor"></div>
		</div>
	</div>
	<div class="buttonbar">
		<a href="./manage/form" class="btn">'.$iconList.'&nbsp;zur Liste</a>
		<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
		'.UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
			'href'		=> './manage/form/remove/'.$form->formId,
			'class'		=> 'btn btn-danger',
			'disabled'	=> ( $withinBlocks || 1 ) ? 'disabled' : NULL,
			'onclick'	=> 'return confirm("Wirklich ?");',
		) ).'
	</div>
</form>
<div class="row-fluid">
	<div class="span6">
		<h4>Verwendete Blöcke</h4>
		'.$listBlocksWithin.'
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h4>Regeln</h4>
			<div class="content-panel-inner">
				'.$listRules.'
				<div class="buttonbar">
					'.$modalTrigger->render().'
				</div>
			</div>
		</div>
	</div>
</div>
'.$modal->render().'
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.3.3/ace.js"></script>
<script>
jQuery(document).ready(function(){
	FormEditor.applyAceEditor("#input_content");
});
</script>';
