<?php
$optCardType	= UI_HTML_Elements::Options( $wordsCards );
$iconNext		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-arrow-right" ) );

return '
<div class="content-panel">
	<h3>Einzahlung mit Kreditkarte <small class="muted">(ohne Registsrierung)</small></h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/wallet/payin/cardWeb/'.$walletId.'" method="post">
			<input type="hidden" name="forwardTo" value="manage/my/mangopay/wallet/view/'.$walletId.'">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_amount">Geldbetrag</label>
					<input type="number" step="0.01" min="0" max="1000" name="amount" id="input_amount" class="span12" value="'.htmlentities( $amount, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span4">
					<label for="input_currency">Währung</label>
					<div class="value">'.$wallet->Balance->Currency.'</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_cardType">Kartenanbieter</label>
					<select name="cardType" id="input_cardType" class="span12">'.$optCardType.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary">'.$iconNext.' weiter</button>
			</div>
		</form>
	</div>
</div>';
