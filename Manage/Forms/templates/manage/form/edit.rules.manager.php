<?php

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconMail	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-envelope' ) );

$optMailManager	= array();
//if( count( $mailsManager ) != 1 )
//	$optMailManager['']	= '- keine -';
foreach( $mailsManager as $item )
	$optMailManager[$item->mailId]	= $item->title;
$optMailManager	= UI_HTML_Elements::Options( $optMailManager, $form->managerMailId );

$listRules	= UI_HTML_Tag::create( 'div', 'Keine Regeln vorhanden.', array( 'class' => 'alert alert-info' ) );
if( $rulesManager ){
	$listRules	= array();
	foreach( $rulesManager as $rule ){
		$mail	= '<em>unbekannt</em>';
		foreach( $mailsManager as $item ){
			if( $item->mailId == $rule->mailId ){
				$mail	= UI_HTML_Tag::create( 'a', $iconMail.'&nbsp;'.$item->title, array(
					'href'	=> './manage/form/mail/edit/'.$item->mailId,
				) );
			}
		}

		$list	= array();
		foreach( json_decode( $rule->rules ) as $item ){
			$keyLabel	= UI_HTML_Tag::create( 'acronym', $item->keyLabel, array( 'title' => 'Interner Schlüssel: '.$item->key ) );
			$valueLabel	= UI_HTML_Tag::create( 'acronym', $item->valueLabel, array( 'title' => 'Interner Schlüssel: '.$item->value ) );
			$list[]	= UI_HTML_Tag::create( 'li', $keyLabel.' => '.$valueLabel );
		}
		$list	= UI_HTML_Tag::create( 'ul', $list, array( 'style' => 'margin-bottom: 0' ) );

		$addresses		= preg_split( '/\s*,\s*/', $rule->mailAddresses );
		foreach( $addresses as $nr => $address )
			foreach( $mailDomains as $domain )
				$addresses[$nr]	= preg_replace( '/'.preg_quote( $domain, '/' ).'$/', '...', $address );

		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> './manage/form/removeRule/'.$form->formId.'/'.$rule->formRuleId,
			'class'	=> 'btn btn-danger btn-small',
		) );
		$listRules[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $list ),
			UI_HTML_Tag::create( 'td', $mail ),
			UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', join( '<br/>', $addresses ) ) ),
			UI_HTML_Tag::create( 'td', $buttonRemove ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array( '', '25%', '20%', '60px' ) );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Regeln', 'E-Mail', 'Empfänger' ) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $listRules );
	$listRules	= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-striped' ) );
}

$modal	= new \CeusMedia\Bootstrap\Modal( 'rule-manager-add' );
$modal->setHeading( 'Neue Manager-Regel' );
$modal->setFormAction( './manage/form/addRule/'.$form->formId.'/'.Model_Form_Rule::TYPE_MANAGER );
$modal->setSubmitButtonLabel( 'speichern' );
$modal->setSubmitButtonClass( 'btn btn-primary' );
$modal->setCloseButtonLabel( 'abbrechen' );
$modal->setBody( '
<div class="row-fluid">
	<div class="span6">
		<label for="input_manager_ruleKey_0" class="mandatory required">Feld</label>
		<select name="ruleKey_0" id="input_manager_ruleKey_0" class="span12" required="required"><option value="">- bitte wählen -</option></select>
	</div>
	<div class="span6">
		<label for="input_manager_ruleValue_0">Wert</label>
		<select name="ruleValue_0" id="input_manager_ruleValue_0" class="span12"></select>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<label for="input_manager_ruleKey_1">Feld</label>
		<select name="ruleKey_1" id="input_manager_ruleKey_1" class="span12"><option value="">- bitte wählen -</option></select>
	</div>
	<div class="span6">
		<label for="input_manager_ruleValue_1">Wert</label>
		<select name="ruleValue_1" id="input_manager_ruleValue_1" class="span12"></select>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<label for="input_manager_ruleKey_2">Feld</label>
		<select name="ruleKey_2" id="input_manager_ruleKey_2" class="span12"><option value="">- bitte wählen -</option></select>
	</div>
	<div class="span6">
		<label for="input_manager_ruleValue_2">Wert</label>
		<select name="ruleValue_2" id="input_manager_ruleValue_2" class="span12"></select>
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
		<label for="input_mailId" class="mandatory required">Manager-E-Mail</label>
		<select name="mailId" id="input_mailId" class="span12" required="required">'.$optMailManager.'</select>
	</div>
</div>
<input type="hidden" name="ruleKeyLabel_0" id="input_manager_ruleKeyLabel_0"/>
<input type="hidden" name="ruleKeyLabel_1" id="input_manager_ruleKeyLabel_1"/>
<input type="hidden" name="ruleKeyLabel_2" id="input_manager_ruleKeyLabel_2"/>
<input type="hidden" name="ruleValueLabel_0" id="input_manager_ruleValueLabel_0"/>
<input type="hidden" name="ruleValueLabel_1" id="input_manager_ruleValueLabel_1"/>
<input type="hidden" name="ruleValueLabel_2" id="input_manager_ruleValueLabel_2"/>
' );
$modalTrigger	= new \CeusMedia\Bootstrap\Modal\Trigger( 'rule-manager-add-trigger' );
$modalTrigger->setModalId( 'rule-manager-add' )->setLabel( $iconAdd.'&nbsp;neue Manager-Regel' );
$modalTrigger->setAttributes( array( 'class' => 'btn btn-primary' ) );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<div class="content-panel-inner">
				'.$listRules.'
				<div class="buttonbar">
					'.$navButtons['list'].'
					'.$navButtons['prevContent'].'
					'.$modalTrigger->render().'
					'.$navButtons['nextCustomer'].'
				</div>
			</div>
		</div>
	</div>
</div>'.$modal->render();
?>
