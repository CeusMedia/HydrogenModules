<?php
$buttonPayIn	= '<a href="./manage/my/mangopay/card/payin/'.$cardId.'?from=manage/my/mangopay/card/'.$cardId.'" class="btn btn-small"><b class="fa fa-sign-in"></b> Einzahlung</a>';
$buttonPayOut	= '<a href="./manage/my/mangopay/card/payOut/'.$cardId.'" class="btn btn-small"><b class="fa fa-sign-out"></b> Auszahlung</a>';
$buttonPreAuth	= '<a href="./manage/my/mangopay/card/payin/preAuthorized/'.$cardId.'" class="btn btn-small"><b class="fa fa-sign-in"></b> Einzahlung mit Reservierung</a>';
if( 0 && !$card->Active ){
	$buttonPayIn	= '<button type="button" class="btn btn-small" disabled="disabled"><b class="fa fa-sign-in"></b> Einzahlung</button>';
	$buttonPreAuth	= '<button type="button" class="btn btn-small" disabled="disabled"><b class="fa fa-sign-in"></b> Einzahlung mit Reservierung</a>';
	$buttonPayOut	= '<button type="button" class="btn btn-small" disabled="disabled"><b class="fa fa-sign-out"></b> Auszahlung</button>';
}

$linkBack	= './'.( $backwardTo ? $backwardTo : 'manage/my/mangopay/card' );

$helperCardNumber	= new View_Helper_Mangopay_Entity_CardNumber( $env );


$panelView	= '
<div class="content-panel panel-mangopay-view" id="panel-mangopay-card-view">
	<h3><i class="fa fa-fw fa-credit-card"></i> Kreditkarte</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span3">
				<label>Anbieter</label>
				<div class="value">'.$card->CardProvider.'</div>
			</div>
			<div class="span6">
				<label>Card Number</label>
				<div class="value">'.$helperCardNumber->set( $card->Alias )->render().'</div>
			</div>
			<div class="span3">
				<label>Gültig bis</label>
				<div class="value">'.$card->ExpirationDate.'</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span4">
				<label>Land</label>
				<div class="value">'.$card->Country.'</div>
			</div>
			<div class="span4">
				<label>Währung</label>
				<div class="value">'.$card->Currency.'</div>
			</div>
			<div class="span4">
				<label>Registriert</label>
				<div class="value">'.date( 'd.m.Y', $card->CreationDate ).'</div>
			</div>
		</div>
<!--		'.print_m( $card, NULL, NULL, TRUE ).'-->
		<div class="buttonbar">
			<a href="'.$linkBack.'" class="btn"><b class="fa fa-arrow-left"></b> zurück</a>
			&nbsp;|&nbsp;
			'.$buttonPayIn.'
			'.$buttonPreAuth.'
			&nbsp;|&nbsp;
			'.$buttonPayOut.'
		</div>
	</div>
</div>';


$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-remove" ) );
$buttonRemove	= UI_HTML_Tag::create( 'button', $iconRemove.' entfernen', array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-danger',
) );

$panelRemove	= '
<div class="content-panel panel-mangopay-view" id="panel-mangopay-card-view">
	<h3><i class="fa fa-fw fa-credit-card"></i> Kreditkarte abmelden</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/card/deactivate/'.$cardId.'" method="post">
			<p>
				...
			</p>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_password">Passwort</label>
					<input type="password" name="password" id="input_password"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonRemove.'
			</div>
		</form>
	</div>
</div>';

return '
<div class="row-fluid">
	<div class="span6">
		'.$panelView.'
	</div>
	<div class="span6">
		'.$panelRemove.'
		'./*$panelTransactions.*/'
	</div>
</div>';
