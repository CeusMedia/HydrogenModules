<?php

//print_m( $words['cardProviders'] );die;

$helperCardLogo	= new View_Helper_Mangopay_Entity_CardProviderLogo( $env );
$helperCardLogo->setSize( View_Helper_Mangopay_Entity_CardProviderLogo::SIZE_SMALL );
$helperCardLogo->setNodeName( 'span' );

$cardTypes	= array(
	'VISA'			=> (object) array(
		'provider'		=> $words['cardProviders']['VISA'],
		'type'			=> 'CB_VISA_MASTERCARD',
	),
	'MASTERCARD'	=> (object) array(
		'provider'		=> $words['cardProviders']['MASTERCARD'],
		'type'			=> 'CB_VISA_MASTERCARD',
	),
/*	'AMEX'			=> (object) array(
		'provider'		=> $words['cardProviders']['AMEX'],
		'type'			=> 'AMEX',
	),*/
	'MAESTRO'		=> (object) array(
		'provider'		=> $words['cardProviders']['MAESTRO'],
		'type'			=> 'MAESTRO',
	),
/*	'MASTERPASS'		=> (object) array(
		'provider'		=> $words['cardProviders']['MASTERPASS'],
		'type'			=> 'MASTERPASS',
	),*/
	'DINERS'		=> (object) array(
		'provider'		=> $words['cardProviders']['DINERS'],
		'type'			=> 'DINERS',
	),
);
foreach( $cardTypes as $cardTypeKey => $cardTypeItem ){
	$logo	= $helperCardLogo->setProvider( $cardTypeKey )->render();
	$link	= UI_HTML_Tag::create( 'a', $logo.'&nbsp;'.$cardTypeItem->provider, array(
		'href'	=> './manage/my/mangopay/card/registration?cardType='.$cardTypeItem->type.'&cardProvider='.$cardTypeKey.'&forwardTo='.$forwardTo,
	) );
	$list[]	= UI_HTML_Tag::create( 'li', $link, array(
		'class'	=> $cardProvider == $cardTypeKey ? 'active' : NULL,
	) );
}
$inputCardType	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );

$part1		= '
<h4>Schritt 1</h4>
<p>Bitte wählen Sie den Anbieter Ihrer Kreditkarte!</h4>
'.$inputCardType.'
';

$part2	= '
	<h4>Schritt 2</h4>
 	<p>Bitte geben Sie folgende Daten zut Kreditkarte an.</p>
	<div class="row-fluid">
		<div class="span6">
			<label for="input_cardNumber">Kartennummer<!--Card Number--></label>
			'.UI_HTML_Tag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'cardNumber',
				'id'			=> 'input_cardNumber',
				'class'			=> 'span12',
				'required'		=> 'required',
			) ).'
		</div>
	</div>
	<div class="row-fluid">
		<div class="span3">
			<label for="cardDate">Gültig bis<!--Expiration Date--></label>
			<input type="text" name="cardDate" id="input_cardDate" value="" class="span12" required="required"/>
		</div>
		<div class="span9">
			<div class="alert">
				Format: MM/JJ<br/>
				Beispiel: 01/20 oder 12/19
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span3">
			<label for="input_cardCvx">CVV</label>
			<input type="text" name="cardCvx" value="" id="input_cardCvx" class="span12" required="required"/>
		</div>
		<div class="span9">
			<div class="alert">
				Diese Angabe finden Sie auf der Rückseite der Kreditkarte.
				Es ist die 3-stellige Zahl rechts neben Ihrer Unterschrift.
				<a href="https://www.cvvnumber.com/" target="_blank">mehr</a>
			</div>
		</div>
	</div>
	<div class="alert alert-info">
		<small><strong>Hinweis zum Datenschutz:</strong><br/>Diese Daten werden lediglich zur Registrierung der Kreditkarte verwendet. Es findet keine Speicherung in unserem System statt.</small>
	</div>
';

if( $cardType ){
	$linkBack	= 'manage/my/mangopay/card/registration';
	if( $backwardTo )
		$linkBack	.= '?backwardTo='.$backwardTo;
	$form	= '
		<form action="'.$registration->CardRegistrationURL.'" method="post">
			<input type="hidden" name="data" value="'.$registration->PreregistrationData.'" />
			<input type="hidden" name="accessKeyRef" value="'.$registration->AccessKey.'" />
			<input type="hidden" name="returnURL" value="'.$returnUrl.'" />
			<input type="hidden" name="cardExpirationDate" id="input_cardExpirationDate"/>
			<div class="row-fluid">
				<div class="span4">
					'.$part1.'
				</div>
				<div class="span8">
					'.$part2.'
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
		<form action="./manage/my/mangopay/card/registration" method="post">
			<input type="hidden" name="backwardTo" value="'.$backwardTo.'"/>
			<input type="hidden" name="forwardTo" value="'.$forwardTo.'"/>
			<div class="row-fluid">
				<div class="span4">
					'.$part1.'
				</div>
			</div>
			<div class="buttonbar">
				<a href="'.$linkBack.'" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>
			</div>
		</form>';
}
return '
<div class="content-panel">
	<h3>Register a new Credit Card</h3>
	<div class="content-panel-inner">
		'.$form.'
	</div>
</div>
<style>
ul.nav li a img {
	padding-right: 10px;
	}
ul.nav li a {
	font-size: 1.1em;
	}
input.success {
	background-color: rgba(140, 255, 120, 0.25);
	}
input.error {
	background-color: rgba(255, 140, 120, 0.25);
	}
</style>
<script>

jQuery(document).ready(function(){
	ModulePaymentMangopayCardRegistration.cardProvider = "'.$cardProvider.'";
	ModulePaymentMangopayCardRegistration.init();
});
</script>';
?>
