<?php

$optWallet	= array();
foreach( $wallets as $item )
	if( $item->Currency == $card->Currency )
		$optWallet[$item->Id]	= $item->Description.' ('.$view->formatMoney( $item->Balance, ' ', 0 ).')';

$optWallet	= UI_HTML_Elements::Options( $optWallet, $walletId );

return '
<div class="content-panel">
	<h3>Pay in from Credit Card to Wallet</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/card/payin/'.$cardId.'" method="post">
			<input type="hidden" name="from" value="'.$from.'"/>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_amount">Amount</label>
					<input type="text" id="input_amount" name="amount"/>
				</div>
				<div class="span6">
					<label for="input_walletId">Wallet</label>
					<select id="input_walletId" name="walletId">'.$optWallet.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/my/mangopay/card" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>
				<button type="submit" name="save" value="payin" class="btn btn-primary"><b class="fa fa-check"></b> einzahlen</button>
			</div>
		</form>
	</div>
</div>';
?>
