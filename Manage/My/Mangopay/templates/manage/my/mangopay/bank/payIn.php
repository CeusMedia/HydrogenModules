<?php

$optWallet	= array();
foreach( $wallets as $item )
	$optWallet[$item->Id]	= $item->Description.' ('.$view->formatMoney( $item->Balance, ' ', 0 ).')';

$optWallet	= UI_HTML_Elements::Options( $optWallet/*, $walletId*/ );

return '
<div class="content-panel">
	<h3>Pay in to Wallet from Bank Account</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/bank/payIn/'.$bankAccountId.'" method="post">
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
