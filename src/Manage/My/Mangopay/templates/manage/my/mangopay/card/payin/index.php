<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconPayin		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-sign-in'] );
$iconCard		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-credit-card'] );

$wordsCurrencies	= [
	'EUR'		=> 'EUR',
	'USD'		=> 'USD',
];

//Logic_Payment_Mangopay::$typeCurrencies;
//$possibleCurrencies	= [];

$currencies	= [];
foreach( $wallets as $item )
	if( !in_array( $item->Currency, $currencies ) )
		$currencies[$item->Currency]	= $wordsCurrencies[$item->Currency];

$optCurrency	= [];
foreach( $currencies as $key => $label )
	$optCurrency[$key]	= $label;
$optCurrency	= HtmlElements::Options( $optCurrency );

$inputCurrency	= HtmlTag::create( 'select', $optCurrency, array(
	'id'		=> 'input_currency',
	'name'		=> 'currency',
	'class'		=> 'span12',
	'readonly'	=> count( $currencies ) == 1 ? 'readonly' : NULL,
	'oninput'	=> 'ModulePaymentMangopayBankPayin.onCurrencyChange()',
) );

$inputWalletId	= '';
if( count( $wallets ) && ( $walletLocked || count( $wallets ) == 1 ) ){
	$helperWallet	= new View_Helper_Mangopay_Entity_Wallet( $env );
	$helperWallet->set( $wallets[0] )->setNodeName( 'div' )->setNodeClass( 'value' );
	$inputWalletId	= '
			<label>in das Portmoney</label>
			'.$helperWallet->render().'<input type="hidden" name="walletId" value="'.$wallets[0]->Id.'"/>';
}
else if( count( $wallets ) > 1 ){
	$optWallet	= [];
	foreach( $wallets as $item )
		$optWallet[$item->Id]	= $item->Description.' ('.$view->formatMoney( $item->Balance, ' ', 0 ).')';
	$optWallet	= HtmlElements::Options( $optWallet, $walletId );
	$inputWalletId	= '
			<label for="input_walletId">in das Portmoney</label>
			<select id="input_walletId" name="walletId" class="span12">'.$optWallet.'</select>';
}

$backwardTo	= isset( $backwardTo ) ? $backwardTo : '';
$forwardTo	= isset( $forwardTo ) ? $forwardTo : '';
$from		= isset( $from ) ? $from : '';

$linkBack	= '';
if( $backwardTo/* || count( $cards ) > 1*/ ){
	$linkBack	= $backwardTo ?: './manage/my/mangopay/card';
	$linkBack	= HtmlTag::create( 'a', $iconCancel.' abbrechen', ['href' => $linkBack, 'class' => "btn"] );
}

$helperCard	= new View_Helper_Mangopay_Entity_Card( $env );
$helperCard->set( $card )->setNodeName( 'div' );

$panelPayIn	= '<div class="content-panel">
	<h3>'.$iconCard.' Von Kreditkarte einzahlen</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/card/payin/'.$cardId.'" method="post">
			<input type="hidden" name="forwardTo" value="'.( isset( $forwardTo ) ? $forwardTo : '' ).'"/>
			<input type="hidden" name="backwardTo" value="'.( isset( $backwardTo ) ? $backwardTo : '' ).'"/>
			<input type="hidden" name="from" value="'.( isset( $from ) ? $from : '' ).'"/>
			<input type="hidden" name="walletId" value="'.$walletId.'"/>
			<div class="row-fluid">
				<div class="span8">
					<label for="input_amount">Geldbetrag</label>
					<input type="number" step="0.01" min="1" max="1000" id="input_amount" name="amount" class="span12" required="required"/>
				</div>
				<div class="span4">
					<label>Währung</label>
					'.$inputCurrency.'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label>von der Kreditkarte</label>
					'.$helperCard.'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					'.$inputWalletId.'
				</div>
			</div>
			<div class="buttonbar">
				'.$linkBack.'
				<button type="submit" name="save" value="payin" class="btn btn-primary">'.$iconSave.' einzahlen</button>
			</div>
			</div>
		</form>
	</div>
</div>';

return '
<div class="row-fluid">
	<div class="span6">
		'.$panelPayIn.'
	</div>
</div>';
