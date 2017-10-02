<?php

$helperCard	= new View_Helper_Mangopay_Entity_Card( $env );
$helperCard->set( $card )->setNodeName( 'div' );

$inputWalletId	= '';
if( count( $wallets ) && ( $walletLocked || count( $wallets ) == 1 ) ){
	$helperWallet	= new View_Helper_Mangopay_Entity_Wallet( $env );
	$helperWallet->set( $wallets[0] )->setNodeName( 'div' )->setNodeClass( 'value' );
	$inputWalletId	= '
			<label>Wallet</label>
			'.$helperWallet->render().'';
}
else if( count( $wallets ) > 1 ){
	$optWallet	= array();
	foreach( $wallets as $item )
		$optWallet[$item->Id]	= $item->Description.' ('.$view->formatMoney( $item->Balance, ' ', 0 ).')';
	$optWallet	= UI_HTML_Elements::Options( $optWallet, $walletId );
	$inputWalletId	= '
			<label for="input_walletId">Wallet</label>
			<select id="input_walletId" name="walletId" class="span12">'.$optWallet.'</select>';
}

$linkBack	= $from ? $from : './manage/my/mangopay/card/'.$cardId;

$panelPayIn	= '<div class="content-panel">
	<h3><i class="fa fa-fw fa-sign-in"></i> Von Kreditkarte einzahlen</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/card/payin/'.$cardId.'" method="post">
			<input type="hidden" name="from" value="'.$from.'"/>
			<input type="hidden" name="walletId" value="'.$walletId.'"/>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_amount">Amount</label>
					<input type="number" step="0.01" min="1" max="1000" id="input_amount" name="amount" class="span10"/>&nbsp;<big>&euro;</big>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span8">
					<label>Kreditkarte</label>
					'.$helperCard.'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span8">
					'.$inputWalletId.'
				</div>
			</div>
			<div class="buttonbar">
				<a href="'.$linkBack.'" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>
				<button type="submit" name="save" value="payin" class="btn btn-primary"><b class="fa fa-check"></b> einzahlen</button>
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
?>
