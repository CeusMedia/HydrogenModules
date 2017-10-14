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

$wallets	= array_slice( $wallets, 3, 3 );

$currencies	= array();
foreach( $wallets as $item )
	if( !in_array( $item->Currency, $currencies ) )
		$currencies[$item->Currency]	= $wordsCurrencies[$item->Currency];

$optCurrency	= array();
foreach( $currencies as $key => $label )
	$optCurrency[$key]	= $label;
$optCurrency	= UI_HTML_Elements::Options( $optCurrency );

$inputCurrency	= UI_HTML_Tag::create( 'select', $optCurrency, array(
	'id'		=> 'input_currency',
	'name'		=> 'currency',
	'class'		=> 'span12',
	'readonly'	=> count( $currencies ) == 1 ? 'readonly' : NULL,
	'oninput'	=> 'ModulePaymentMangopayBankPayin.onCurrencyChange()',
) );

$optWallet		= array();
foreach( $wallets as $item )
	$optWallet[$item->Id]	= $item->Description.' ('.$view->formatMoney( $item->Balance, ' ', 0 ).')';
$optWallet		= UI_HTML_Elements::Options( $optWallet );
$inputWallet	= UI_HTML_Tag::create( 'select', $optWallet, array(
	'id'		=> 'input_walletId',
	'name'		=> 'walletId',
	'class'		=> 'span12',
	'readonly'	=> count( $currencies ) == 1 ? 'readonly' : NULL,
) );

if( $currencyFirst )
	$fieldMoney	= '<div class="row-fluid">
		<div class="span4">
			<label for="input_currency">Währung</label>
			'.$inputCurrency.'
		</div>
		<div class="span8">
			<label for="input_amount">Geldbetrag</label>
			<input type="number" step="0.01" min="1" max="1000" id="input_amount" name="amount" class="span12" value="'.htmlentities( $amount, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
	</div>';
else
	$fieldMoney	= '<div class="row-fluid">
		<div class="span8">
			<label for="input_amount">Geldbetrag</label>
			<input type="number" step="0.01" min="1" max="1000" id="input_amount" name="amount" class="span12" value="'.htmlentities( $amount, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span4">
			<label for="input_currency">in Währung</label>
			'.$inputCurrency.'
		</div>
	</div>';

$linkBack	= $from ? $from : './manage/my/mangopay/bank/view/'.$bankAccountId;

$helperIBAN	= new View_Helper_Mangopay_Entity_IBAN( $env );
$helperIBAN->set( $bankAccount->Details->IBAN );
$helperIBAN->setNodeName( 'small' );

$helperBIC	= new View_Helper_Mangopay_Entity_BIC( $env );
$helperBIC->set( $bankAccount->Details->BIC );
$helperBIC->setNodeName( 'small' );


$panelAdd	= '
<div class="content-panel">
	<h3>Überweisung vom Bankkonto</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/bank/payin/'.$bankAccountId.'" method="post">
			<input type="hidden" name="from" value="'.$from.'"/>
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
				<a href="'.$linkBack.'" class="btn">'.$iconCancel.' zurück</a>
				<button type="submit" name="save" value="payin" class="btn btn-primary">'.$iconSave.' Überweisung anmelden</button>
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
