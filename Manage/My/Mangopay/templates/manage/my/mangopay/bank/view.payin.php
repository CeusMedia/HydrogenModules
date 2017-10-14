<?php

$iconPayin		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sign-in' ) );
$iconPayout		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sign-out' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$hintPayin			= 'Manuelle Überweisung mit Online- oder Offline-Banking.<br/>Ausführung benötigt 1-2 Werktage.';
$hintPayin			= UI_HTML_Tag::create( 'small', $hintPayin, array( 'class' => 'half-muted' ) );
$labelPayin			= $iconPayin.' per Überweisung'.$hintPayin;
$buttonPayin		= UI_HTML_Tag::create( 'a', $labelPayin, array(
	'href'	=> './manage/my/mangopay/bank/payin/'.$bankAccountId,
	'class'	=> 'btn btn-block',
	'style'	=> 'text-align: left',
) );

$hintPayinWeb		= 'Gutschrift erfolgt sofort.<br/>Eingabe von IBAN und Online-Banking-PIN notwendig.';
$hintPayinWeb		= UI_HTML_Tag::create( 'small', $hintPayinWeb, array( 'class' => 'half-muted' ) );
$labelPayinWeb		= $iconPayin.' per Sofortüberweisung'.$hintPayinWeb;
$buttonPayinWeb		= UI_HTML_Tag::create( 'a', $labelPayinWeb, array(
	'href'	=> '#',
	'class'	=> 'btn btn-block',
	'style'	=> 'text-align: left',
) );

$labelPayinDebit	= 'per Lastschrift';
if( count( $mandates ) ){
	$hintPayinDebit		= 'Gutschrift erfolgt sofort.<br/>Konto muss über den Betrag gedeckt sein.';
	$hintPayinDebit		= UI_HTML_Tag::create( 'small', $hintPayinDebit, array( 'class' => 'half-muted' ) );
	$labelPayinDebit	= $iconPayin.' '.$labelPayinDebit.$hintPayinDebit;
	$buttonPayinDebit	= UI_HTML_Tag::create( 'a', $labelPayinDebit, array(
		'href'		=> '#',
		'class'		=> 'btn btn-block',
		'style'		=> 'text-align: left',
		'disabled'	=> !count( $mandates ) ? 'disabled' : NULL,
	) );
}
else{
	$hintPayinDebit		= 'Nur möglich mit Lastschriftmandat.';
	$hintPayinDebit		= UI_HTML_Tag::create( 'small', $hintPayinDebit, array( 'class' => 'not-muted' ) );
	$labelPayinDebit	= $iconPayin.' '.$labelPayinDebit.$hintPayinDebit;
	$buttonPayinDebit	= UI_HTML_Tag::create( 'button', $labelPayinDebit, array(
		'type'		=> 'button',
		'class'		=> 'btn btn-block',
		'style'		=> 'text-align: left',
		'disabled'	=> !count( $mandates ) ? 'disabled' : NULL,
	) );
}

$hintPayout			= 'Muss mit Passwort bestätigt werden.<br/>Ausführung benötigt 1-2 Werktage.';
$hintPayout			= UI_HTML_Tag::create( 'small', $hintPayout, array( 'class' => 'half-muted' ) );
$labelPayout		= $iconPayout.' per Überweisung'.$hintPayout;
$buttonPayout		= UI_HTML_Tag::create( 'a', $labelPayout, array(
	'href'	=> '#',
	'class'	=> 'btn btn-block',
	'style'	=> 'text-align: left',
) );



return '
<div class="content-panel">
	<h3>Was ist zu tun?</h3>
	<div class="content-panel-inner">
		<h4>Einzahlen vom Bankkonto</h4>
		<p><small>
			Einen Geldbetrag vom Bankonto zum Portmoney überweisen.
			Dazu gibt es mehrere Möglichten, die sich in Geschwindigkeit und Aufwand unterscheiden.<br/>
		</small></p>
		'.$buttonPayin.'
		'.$buttonPayinWeb.'
		'.$buttonPayinDebit.'
		<h4>Auszahlen zum Bankkonto</h4>
		<p><small>Vom Guthaben an das Bankkonto auszahlen. Beispielsweise wenn zuviel eingezahlt wurde.</small></p>
		'.$buttonPayout.'
	</div>
</div>';

$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$wordsCurrencies	= array(
	'EUR'		=> 'EUR',
	'USD'		=> 'USD',
);

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
));
if( count( $currencies ) == 1 )
	$inputCurrency	= UI_HTML_Tag::create( 'select', $optCurrency, array(
		'id'		=> 'input_currency',
		'name'		=> 'currency',
		'class'		=> 'span12',
		'disabled'	=> 'disabled',
	));


if( count( $wallets ) > 1 ){
	$optWallet	= array();
	foreach( $wallets as $item )
		$optWallet[$item->Id]	= $item->Description.' ('.$view->formatMoney( $item->Balance, ' ', 0 ).')';
	$optWallet	= UI_HTML_Elements::Options( $optWallet );

	$fieldWallet	= '
				<div class="span6">
					<label for="input_walletId">Wallet</label>
					<select id="input_walletId" name="walletId" class="span12">'.$optWallet.'</select>
				</div>';
}
else{
	$fieldWallet	= UI_HTML_Tag::create( 'input', NULL, array(
		'type'	=> 'hidden',
		'name'	=> 'walletId',
		'value'	=> $wallets[0]->Id
	) );
}

return '
<div class="content-panel">
	<h3>Überweisung vom Bankkonto</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/bank/payin/'.$bankAccountId.'" method="post">
			<input type="hidden" name="from" value="manage/my/mangopay/bank/view/'.$bankAccountId.'"/>
			<div class="row-fluid">
				<div class="span2">
					<label for="input_currency">Währung</label>
					'.$inputCurrency.'
				</div>
				<div class="span4">
					<label for="input_amount">Amount</label>
					<input type="number" step="0.01" min="1" max="1000" id="input_amount" name="amount" class="span10" value=""/>&nbsp;<big>&euro;</big>
				</div>
				'.$fieldWallet.'
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" value="payin" class="btn btn-primary">'.$iconSave.' Überweisung anmelden</button>
			</div>
		</form>
	</div>
</div>';
?>
