<?php

$optType	= UI_HTML_Elements::Options( $words['cardTypes'], $cardType );


if( $cardType ){
	$linkBack	= 'manage/my/mangopay/card/add';
	if( $backwardTo )
		$linkBack	.= '?backwardTo='.$backwardTo;
	$form	= '
		<form action="'.$registration->CardRegistrationURL.'" method="post">
			<input type="hidden" name="data" value="'.$registration->PreregistrationData.'" />
			<input type="hidden" name="accessKeyRef" value="'.$registration->AccessKey.'" />
			<input type="hidden" name="returnURL" value="'.$returnUrl.'" />
			<div class="row-fluid">
				<div class="span3">
					<label for="input_cardType">Card Type</label>
					<select name="cardType" id="input_cardType" class="span12" readonly="readonly">'.$optType.'</select>
				</div>
				<div class="span9">
					<label for="input_title">Bezeichnung <small class="muted">(z.B. "Meine VISA-Karte")</small></label>
					<input type="text" name="title" id="input_title" class="span12" readonly="readonly" value="'.htmlentities( $cardTitle, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<hr/>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_cardNumber">Card Number</label>
					<input type="text" name="cardNumber" id="input_cardNumber" value="" class="span12"/>
				</div>
				<div class="span3">
					<label for="input_cardExpirationDate">Expiration Date</label>
					<input type="text" name="cardExpirationDate" id="input_cardExpirationDate" value="" class="span12"/>
				</div>
				<div class="span3">
					<label for="input_cardCvx">CVV</label>
					<input type="text" name="cardCvx" value="" id="input_cardCvx" class="span12"/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="'.$linkBack.'" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>
				<button type="submit" name="save" value="register" class="btn btn-primary"><b class="fa fa-check"></b> registrieren</button>
			</div>
		</form>';
}
else{
	$linkBack	= './'.( $backwardTo ? $backwardTo : 'manage/my/mangopay/card' );
	$form	= '
		<form action="./manage/my/mangopay/card/add" method="post">
			<input type="hidden" name="backwardTo" value="'.$backwardTo.'"/>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_cardType">Card Type</label>
					<select name="cardType" id="input_cardType" class="span12">'.$optType.'</select>
				</div>
				<div class="span9">
					<label for="input_title">Bezeichnung <small class="muted">(z.B. "Meine VISA-Karte")</small></label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="'.$linkBack.'" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>
				<button type="submit" name="save" value="select" class="btn btn-primary"><b class="fa fa-check"></b> weiter</button>
			</div>
		</form>';
}
return '
<div class="content-panel">
	<h3>Register a new Credit Card</h3>
	<div class="content-panel-inner">
		'.$form.'
	</div>
</div>';
?>
