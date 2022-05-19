<?php

$currencyFirst = FALSE;

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconList		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconPrint		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-print' ) );

$wordsCurrencies	= array(
	'EUR'		=> 'EUR',
	'USD'		=> 'USD',
);

$currencies	= [];
foreach( $wallets as $item )
	if( !in_array( $item->Currency, $currencies ) )
		$currencies[$item->Currency]	= $wordsCurrencies[$item->Currency];

$optCurrency	= [];
foreach( $currencies as $key => $label )
	$optCurrency[$key]	= $label;
$optCurrency	= UI_HTML_Elements::Options( $optCurrency, isset( $currency ) ? $currency : NULL );

$inputCurrency	= UI_HTML_Tag::create( 'select', $optCurrency, array(
	'id'		=> 'input_currency',
	'name'		=> 'currency',
	'class'		=> 'span12',
	'readonly'	=> count( $currencies ) == 1 ? 'readonly' : NULL,
	'oninput'	=> 'ModulePaymentMangopayBankPayin.onCurrencyChange()',
) );

$optWallet		= [];
foreach( $wallets as $item )
	$optWallet[$item->Id]	= $item->Description.' ('.$view->formatMoney( $item->Balance, ' ', 0 ).')';
$optWallet		= UI_HTML_Elements::Options( $optWallet );
$inputWallet	= UI_HTML_Tag::create( 'select', $optWallet, array(
	'id'		=> 'input_walletId',
	'name'		=> 'walletId',
	'class'		=> 'span12',
	'readonly'	=> count( $currencies ) == 1 ? 'readonly' : NULL,
) );

$helperAmount	= new View_Helper_Mangopay_Input_Amount( $env );
$helperAmount->set( $amount );

if( $currencyFirst )
	$fieldMoney	= '<div class="row-fluid">
		<div class="span4">
			'.UI_HTML_Tag::create( 'label', 'in Währung', array( 'for' => 'input_currency' ) ).'
			'.$inputCurrency.'
		</div>
		<div class="span8">
			'.UI_HTML_Tag::create( 'label', 'Geldbetrag', array( 'for' => 'input_amount' ) ).'
			'.$helperAmount.'
		</div>
	</div>';
else
	$fieldMoney	= '<div class="row-fluid">
		<div class="span8">
			'.UI_HTML_Tag::create( 'label', 'Geldbetrag', array( 'for' => 'input_amount' ) ).'
			'.$helperAmount.'
		</div>
		<div class="span4">
			'.UI_HTML_Tag::create( 'label', 'in Währung', array( 'for' => 'input_currency' ) ).'
			'.$inputCurrency.'
		</div>
	</div>';

$helperIBAN	= new View_Helper_Mangopay_Entity_IBAN( $env );
$helperIBAN->set( $bankAccount->Details->IBAN );
$helperIBAN->setNodeName( 'small' );

$helperBIC	= new View_Helper_Mangopay_Entity_BIC( $env );
$helperBIC->set( $bankAccount->Details->BIC );
$helperBIC->setNodeName( 'small' );

$helperUrl	= new \View_Helper_Mangopay_URL( $env );
$helperUrl->set( ( isset( $from ) && $from ) ? $from :  'manage/my/mangopay/bank/view/'.$bankAccountId );
$helperUrl->setBackwardTo( TRUE );
$helperUrl->setForwardTo( TRUE );
$helperUrl->setFrom( TRUE );
$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array(
	'href'	=> $helperUrl->render(),
	'class'	=> 'btn',
) );
$buttonSave	= UI_HTML_Tag::create( 'button', $iconSave.' Überweisung anmelden', array(
	'type'	=> 'submit',
	'name'	=> "save",
/*	'value'	=> "payin",*/
	'class'	=> 'btn btn-primary',
) );
$panelAdd	= '
<div class="content-panel">
	<h3>Überweisung vom Bankkonto</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/bank/payin/'.$bankAccountId.'" method="post">
			<input type="hidden" name="forwardTo" value="'.( isset( $forwardTo ) ? $forwardTo : '' ).'"/>
			<input type="hidden" name="backwardTo" value="'.( isset( $backwardTo ) ? $backwardTo : '' ).'"/>
			<input type="hidden" name="from" value="'.( isset( $from ) ? $from : '' ).'"/>
			<div class="row-fluid">
				<div class="span12">
					'.$fieldMoney.'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label>vom Bankkonto</label>
					<div class="value">'.$bankAccount->OwnerName.'</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span7">
					<label>IBAN</label>
					<div class="value">'.$helperIBAN.'</div>
				</div>
				<div class="span5">
					<label>BIC <!--<small class="muted">(SWIFT-Code)</small>--></label>
					<div class="value">'.$helperBIC.'</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_walletId">zum Portmoney</label>
					'.$inputWallet.'
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
jQuery(document).ready(function(){
	ModulePaymentMangopayBankPayin.wallets = '.json_encode( $wallets ).';
	ModulePaymentMangopayBankPayin.currencyFirst = '.json_encode( FALSE ).';
	ModulePaymentMangopayBankPayin.numberSeparator = '.json_encode( "," ).';
	ModulePaymentMangopayBankPayin.init();
});
</script>';

return '<div class="row-fluid">
	<div class="span8">
		'.$panelAdd.'
	</div>
	<div class="span4">
	</div>
</div>';
?>
