<?php
$optCardType	= UI_HTML_Elements::Options( $wordsCards );

$optCurrency	= array();
foreach( $wallets as $wallet )
	$optCurrency[$wallet->Balance->Currency]	= $wallet->Balance->Currency;
asort( $optCurrency );
$optCurrency	= UI_HTML_Elements::Options( $optCurrency );

return '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>Pay In (Web)</h3>
			<div class="content-panel-inner">
				<form action="./manage/my/mangopay/wallet/payin/cardWeb/'.$walletId.'" method="post">
					<div class="row-fluid">
						<div class="span8">
							<label for="input_amount">Geldbetrag</label>
							<input type="number" step="0.01" min="0" max="1000" name="amount" id="input_amount" class="span12" value="'.htmlentities( $amount, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span4">
							<label for="input_currency">Währung</label>
							<select name="currency" id="input_currency" class="span12">'.$optCurrency.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_cardType">Kartenanbieter</label>
							<select name="cardType" id="input_cardType" class="span12">'.$optCardType.'</select>
						</div>
					</div>
					<div class="buttonbar">
						<a href="./manage/my/mangopay/wallet/view/'.$walletId.'" class="btn"><i class="fa fa-arrow-left"></i> zurück</a>
						<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-arrow-right"></i> weiter</button>
					<div>
				</form>
			</div>
		</div>
	</div>
</div>';
