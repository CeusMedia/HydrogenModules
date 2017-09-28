<?php

$optWallet	= array();
foreach( $wallets as $item )
	$optWallet[$item->Id]	= $item->Description.' ('.$view->formatMoney( $item->Balance, ' ', 0 ).')';

$optWallet	= UI_HTML_Elements::Options( $optWallet, $walletId );


$inputWalletId	= '';
if( count( $wallets ) > 1 ){
	$inputWalletId	= '
		<div class="span6">
			<label for="input_walletId">Wallet</label>
			<select id="input_walletId" name="walletId">'.$optWallet.'</select>
		</div>
	';
}
else if( count( $wallets ) == 1 ){
	$inputWalletId	= UI_HTML_Tag::create( 'input', NULL, array(
		'type'		=> 'hidden',
		'name'		=> 'walletId',
		'value'		=> $wallets[0]->Id,
	) );
}

return '
<div class="content-panel">
	<h3>PreAuth for pay in from Credit Card to Wallet</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/card/payin/preAuthorized/'.$cardId.'" method="post">
			<input type="hidden" name="from" value="'.$from.'"/>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_amount">Amount</label>
					<input type="number" step="0.01" min="1" max="1000" id="input_amount" name="amount" class="span10"/>&nbsp;<big>&euro;</big>
				</div>
				'.$inputWalletId.'
			</div>
			<div class="buttonbar">
				<a href="./manage/my/mangopay/card" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>
				<button type="submit" name="save" value="payin" class="btn btn-primary"><b class="fa fa-check"></b> einzahlen</button>
			</div>
		</form>
	</div>
</div>';
?>
