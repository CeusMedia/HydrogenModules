<?php
if( isset( $payin ) ){
	return '
<div class="content-panel">
	<h3>Pay In</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/wallet/payIn/'.$walletId.'/bankwire" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_amount">Amount</label>
					<input type="text" name="amount" id="input_amount"/>
				</div>
				<div class="span6">
					<label for="input_currency">Currency</label>
					<input type="text" name="currency" id="input_currency" value="EUR"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" value="bankwire"><b class="fa fa-check"></b> Überweisung vorbereiten</button>
			<div>
		</form>
	</div>
</div>';
	}

return '
<div class="content-panel">
	<h3>Pay In</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/wallet/payIn/'.$walletId.'/bankwire" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_amount">Amount</label>
					<input type="text" name="amount" id="input_amount"/>
				</div>
				<div class="span6">
					<label for="input_currency">Currency</label>
					<input type="text" name="currency" id="input_currency" value="EUR"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" value="bankwire"><b class="fa fa-check"></b> Überweisung vorbereiten</button>
			<div>
		</form>
	</div>
</div>';
