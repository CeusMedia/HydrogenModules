<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as BootstrapModalTrigger;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as Html;

$iconAdd	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconSave	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconRemove	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconMail	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-envelope'] );

$optMailManager	= [];
//if( count( $mailsManager ) != 1 )
//	$optMailManager['']	= '- keine -';
foreach( $mailsManager as $item )
	$optMailManager[$item->mailId]	= $item->title;
$optMailManager	= HtmlElements::Options( $optMailManager, $form->managerMailId );

$listRules	= Html::create( 'div', 'Keine Regeln vorhanden.', ['class' => 'alert alert-info'] );
if( $rulesManager ){
	$listRules	= [];
	foreach( $rulesManager as $rule ){
		$mail	= '<em>unbekannt</em>';
		foreach( $mailsManager as $item ){
			if( $item->mailId == $rule->mailId ){
				$mail	= Html::create( 'a', $iconMail.'&nbsp;'.$item->title, [
					'href'	=> './manage/form/mail/edit/'.$item->mailId,
				] );
			}
		}

		$list	= [];
		foreach( json_decode( $rule->rules ) as $item ){
			$keyLabel	= Html::create( 'acronym', $item->keyLabel, ['title' => 'Interner Schlüssel: '.$item->key] );
			$valueLabel	= Html::create( 'acronym', $item->valueLabel, ['title' => 'Interner Schlüssel: '.$item->value] );
			$list[]	= Html::create( 'li', $keyLabel.' => '.$valueLabel );
		}
		$list	= Html::create( 'ul', $list, ['style' => 'margin-bottom: 0'] );

		$addresses		= preg_split( '/\s*,\s*/', $rule->mailAddresses );
		foreach( $addresses as $nr => $address )
			foreach( $mailDomains as $domain )
				$addresses[$nr]	= preg_replace( '/'.preg_quote( $domain, '/' ).'$/', '...', $address );

		$buttonRemove	= Html::create( 'a', $iconRemove, [
			'href'	=> './manage/form/removeRule/'.$form->formId.'/'.$rule->formRuleId,
			'class'	=> 'btn btn-danger btn-small',
		] );
		$listRules[]	= Html::create( 'tr', array(
			Html::create( 'td', $list ),
			Html::create( 'td', $mail ),
			Html::create( 'td', Html::create( 'small', join( '<br/>', $addresses ) ) ),
			Html::create( 'td', $buttonRemove ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( ['', '25%', '20%', '60px'] );
	$thead		= Html::create( 'thead', HtmlElements::TableHeads( ['Regeln', 'E-Mail', 'Empfänger'] ) );
	$tbody		= Html::create( 'tbody', $listRules );
	$listRules	= Html::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-striped'] );
}

$modal	= new BootstrapModalDialog( 'rule-manager-add' );
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
		<label for="input_manager_mailAddresses" class="mandatory required">E-Mail-Adressen <small class="muted">(getrennt mit Komma)</small></label>
		<input type="text" name="mailAddresses" id="input_manager_mailAddresses" class="span12" required="required"/>
	</div>
</div>
<div class="row-fluid">
	<div class="span8">
		<label for="input_manager_mailId" class="mandatory required">Manager-E-Mail</label>
		<select name="mailId" id="input_manager_mailId" class="span12" required="required">'.$optMailManager.'</select>
	</div>
</div>
<input type="hidden" name="ruleKeyLabel_0" id="input_manager_ruleKeyLabel_0"/>
<input type="hidden" name="ruleKeyLabel_1" id="input_manager_ruleKeyLabel_1"/>
<input type="hidden" name="ruleKeyLabel_2" id="input_manager_ruleKeyLabel_2"/>
<input type="hidden" name="ruleValueLabel_0" id="input_manager_ruleValueLabel_0"/>
<input type="hidden" name="ruleValueLabel_1" id="input_manager_ruleValueLabel_1"/>
<input type="hidden" name="ruleValueLabel_2" id="input_manager_ruleValueLabel_2"/>
' );
$modalTrigger	= new BootstrapModalTrigger();
$modalTrigger->setId( 'rule-manager-add-trigger' );
$modalTrigger->setModalId( 'rule-manager-add' )->setLabel( $iconAdd.'&nbsp;neue Manager-Regel' );
$modalTrigger->setAttributes( ['class' => 'btn btn-primary'] );

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
