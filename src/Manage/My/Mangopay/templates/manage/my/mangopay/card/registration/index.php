<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-arrow-left'] );

$helperCardLogo	= new View_Helper_Mangopay_Entity_CardProviderLogo( $env );
$helperCardLogo->setSize( View_Helper_Mangopay_Entity_CardProviderLogo::SIZE_SMALL );
$helperCardLogo->setNodeName( 'span' );

$cardTypes	= array(
	'VISA'			=> (object) [
		'provider'		=> $words['cardProviders']['VISA'],
		'type'			=> 'CB_VISA_MASTERCARD',
	],
	'MASTERCARD'	=> (object) [
		'provider'		=> $words['cardProviders']['MASTERCARD'],
		'type'			=> 'CB_VISA_MASTERCARD',
	],
/*	'AMEX'			=> (object) [
		'provider'		=> $words['cardProviders']['AMEX'],
		'type'			=> 'AMEX',
	],*/
	'MAESTRO'		=> (object) [
		'provider'		=> $words['cardProviders']['MAESTRO'],
		'type'			=> 'MAESTRO',
	],
/*	'MASTERPASS'		=> (object) [
		'provider'		=> $words['cardProviders']['MASTERPASS'],
		'type'			=> 'MASTERPASS',
	],*/
	'DINERS'		=> (object) [
		'provider'		=> $words['cardProviders']['DINERS'],
		'type'			=> 'DINERS',
	],
);
foreach( $cardTypes as $cardTypeKey => $cardTypeItem ){
	$logo		= $helperCardLogo->setProvider( $cardTypeKey )->render();
	$helperUrl	= new \View_Helper_Mangopay_URL( $env );
	$helperUrl->set( 'manage/my/mangopay/card/registration' );
	$helperUrl->setParameter( 'cardType', $cardTypeItem->type );
	$helperUrl->setParameter( 'cardProvider', $cardTypeKey );
	$helperUrl->setBackwardTo( TRUE );
	$helperUrl->setForwardTo( TRUE );
	$helperUrl->setFrom( TRUE );
	$link	= HtmlTag::create( 'a', $logo.'&nbsp;'.$cardTypeItem->provider, array(
		'href'	=> $helperUrl->render(),
	) );
	$list[]	= HtmlTag::create( 'li', $link, [
		'class'	=> $cardProvider == $cardTypeKey ? 'active' : NULL,
	] );
}
$inputCardType	= HtmlTag::create( 'ul', $list, ['class' => 'nav nav-pills nav-stacked'] );

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
			'.HtmlTag::create( 'input', NULL, [
				'type'			=> 'text',
				'name'			=> 'cardNumber',
				'id'			=> 'input_cardNumber',
				'class'			=> 'span12',
				'required'		=> 'required',
			] ).'
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
	$helperUrl	= new \View_Helper_Mangopay_URL( $env );
	$helperUrl->set( $backwardTo ? $backwardTo : 'manage/my/mangopay/card/registration' );
	$helperUrl->setBackwardTo( $backwardTo ? $backwardTo : NULL );
	$helperUrl->setForwardTo( $forwardTo ? $forwardTo : NULL );
	$helperUrl->setFrom( isset( $from ) ? $from : NULL );
	$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zurück', array( 'href' => $helperUrl->render(), 'class' => 'btn' ) );
	$buttonSave		= HtmlTag::create( 'button', '<b class="fa fa-check"></b> registrieren', [
		'type'		=> "submit",
		'name'		=> "save",
		'value'		=> "register",
		'class'		=> "btn btn-primary"
	] );
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
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>';
}
else{
	$helperUrl	= new \View_Helper_Mangopay_URL( $env );
	$helperUrl->set( $backwardTo ? $backwardTo : 'manage/my/mangopay/card' );
	$helperUrl->setBackwardTo( $backwardTo ? $backwardTo : NULL );
	$helperUrl->setForwardTo( $forwardTo ? $forwardTo : NULL );
	$helperUrl->setFrom( isset( $from ) ? $from : NULL );
	$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zurück', array( 'href' => $helperUrl->render(), 'class' => 'btn' ) );
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
				'.$buttonCancel.'
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
<script>
jQuery(document).ready(function(){
	ModulePaymentMangopayCardRegistration.cardProvider = "'.$cardProvider.'";
	ModulePaymentMangopayCardRegistration.init();
});
</script>';
