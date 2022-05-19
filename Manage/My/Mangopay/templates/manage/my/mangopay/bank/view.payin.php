<?php



$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

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
	$optWallet	= [];
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
