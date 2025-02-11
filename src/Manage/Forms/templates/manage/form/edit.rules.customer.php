<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as BootstrapModalTrigger;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

/** @var object $form */
/** @var array<string,string> $mailsCustomer */
/** @var array<object> $rulesCustomer */
/** @var array<string,string|HtmlTag> $navButtons */


$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconMail	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-envelope'] );

$optMailCustomer	= [];
//if( count( $mailsCustomer ) != 1 )
//	$optMailCustomer['']	= '- keine -';
foreach( $mailsCustomer as $item )
	$optMailCustomer[$item->mailId]	= $item->title;
$optMailCustomer	= HtmlElements::Options( $optMailCustomer, $form->customerMailId );

$listRules	= HtmlTag::create( 'div', 'Keine Regeln vorhanden.', ['class' => 'alert alert-info'] );
if( $rulesCustomer ){
	$listRules	= [];
	foreach( $rulesCustomer as $rule ){
		$mail	= '<em>unbekannt</em>';
		foreach( $mailsCustomer as $item ){
			if( $item->mailId == $rule->mailId ){
				$mail	= HtmlTag::create( 'a', $iconMail.'&nbsp;'.$item->title, [
					'href'	=> './manage/form/mail/edit/'.$item->mailId,
				] );
			}
		}

		$list	= [];
		foreach( json_decode( $rule->rules ) as $item )
			$list[]	= HtmlTag::create( 'li', $item->keyLabel.' => '.$item->valueLabel );
		$list	= HtmlTag::create( 'ul', $list, ['style' => 'margin-bottom: 0'] );

		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
			'href'	=> './manage/form/removeRule/'.$form->formId.'/'.$rule->formRuleId,
			'class'	=> 'btn btn-danger btn-small',
		] );
		$listRules[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $list ),
			HtmlTag::create( 'td', $mail ),
			HtmlTag::create( 'td', $buttonRemove ),
		] );
	}
	$colgroup	= HtmlElements::ColumnGroup( ['', '35%', '60px'] );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['Regeln', 'E-Mail'] ) );
	$tbody		= HtmlTag::create( 'tbody', $listRules );
	$listRules	= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-striped'] );
}

$modal	= new BootstrapModalDialog( 'rule-customer-add' );
$modal->setHeading( 'Neue Kunden-Regel' );
$modal->setFormAction( './manage/form/addRule/'.$form->formId.'/'.Model_Form_Rule::TYPE_CUSTOMER );
$modal->setSubmitButtonLabel( 'speichern' );
$modal->setSubmitButtonClass( 'btn btn-primary' );
$modal->setCloseButtonLabel( 'abbrechen' );
$modal->setBody( '
<div class="row-fluid">
	<div class="span6">
		<label for="input_customer_ruleKey_0" class="mandatory required">Feld</label>
		<select name="ruleKey_0" id="input_customer_ruleKey_0" class="span12" required="required"><option value="">- bitte wählen -</option></select>
	</div>
	<div class="span6">
		<label for="input_customer_ruleValue_0">Wert</label>
		<select name="ruleValue_0" id="input_customer_ruleValue_0" class="span12"></select>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<label for="input_customer_ruleKey_1">Feld</label>
		<select name="ruleKey_1" id="input_customer_ruleKey_1" class="span12"><option value="">- bitte wählen -</option></select>
	</div>
	<div class="span6">
		<label for="input_customer_ruleValue_1">Wert</label>
		<select name="ruleValue_1" id="input_customer_ruleValue_1" class="span12"></select>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<label for="input_customer_ruleKey_2">Feld</label>
		<select name="ruleKey_2" id="input_customer_ruleKey_2" class="span12"><option value="">- bitte wählen -</option></select>
	</div>
	<div class="span6">
		<label for="input_customer_ruleValue_2">Wert</label>
		<select name="ruleValue_2" id="input_customer_ruleValue_2" class="span12"></select>
	</div>
</div>
<div class="row-fluid">
	<div class="span8">
		<label for="input_customer_mailId" class="mandatory required">Kunden-E-Mail</label>
		<select name="mailId" id="input_customer_mailId" class="span12" required="required">'.$optMailCustomer.'</select>
	</div>
</div>
<input type="hidden" name="ruleKeyLabel_0" id="input_customer_ruleKeyLabel_0"/>
<input type="hidden" name="ruleKeyLabel_1" id="input_customer_ruleKeyLabel_1"/>
<input type="hidden" name="ruleKeyLabel_2" id="input_customer_ruleKeyLabel_2"/>
<input type="hidden" name="ruleValueLabel_0" id="input_customer_ruleValueLabel_0"/>
<input type="hidden" name="ruleValueLabel_1" id="input_customer_ruleValueLabel_1"/>
<input type="hidden" name="ruleValueLabel_2" id="input_customer_ruleValueLabel_2"/>
' );
$modalTrigger	= new BootstrapModalTrigger();
$modalTrigger->setId( 'rule-customer-add-trigger' );
$modalTrigger->setModalId( 'rule-customer-add' )->setLabel( $iconAdd.'&nbsp;neue Regel' );
$modalTrigger->setAttributes( ['class' => 'btn btn-primary'] );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<div class="content-panel-inner">
				'.$listRules.'
				<div class="buttonbar">
					'.$navButtons['list'].'
					'.$navButtons['prevManager'].'
					'.$modalTrigger->render().'
<!--					'.$navButtons['nextFills'].'-->
				</div>
			</div>
		</div>
	</div>
</div>'.$modal->render();
