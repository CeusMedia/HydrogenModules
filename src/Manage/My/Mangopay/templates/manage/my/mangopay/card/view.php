<?php
$buttonPayIn	= '<a href="./manage/my/mangopay/card/payin/'.$cardId.'?from=manage/my/mangopay/card/'.$cardId.'" class="btn btn-small"><b class="fa fa-sign-in"></b> Einzahlung</a>';
$buttonPayOut	= '<a href="./manage/my/mangopay/card/payOut/'.$cardId.'" class="btn btn-small"><b class="fa fa-sign-out"></b> Auszahlung</a>';
$buttonPreAuth	= '<a href="./manage/my/mangopay/card/payin/preAuthorized/'.$cardId.'" class="btn btn-small"><b class="fa fa-sign-in"></b> Einzahlung mit Reservierung</a>';
if( 0 && !$card->Active ){
	$buttonPayIn	= '<button type="button" class="btn btn-small" disabled="disabled"><b class="fa fa-sign-in"></b> Einzahlung</button>';
	$buttonPreAuth	= '<button type="button" class="btn btn-small" disabled="disabled"><b class="fa fa-sign-in"></b> Einzahlung mit Reservierung</a>';
	$buttonPayOut	= '<button type="button" class="btn btn-small" disabled="disabled"><b class="fa fa-sign-out"></b> Auszahlung</button>';
}

$linkBack	= './'.( $backwardTo ?: 'manage/my/mangopay/card' );

$helperCardNumber	= new View_Helper_Mangopay_Entity_CardNumber( $env );


$panelView	= '
<div class="content-panel panel-mangopay-view" id="panel-mangopay-card-view">
	<h3><i class="fa fa-fw fa-credit-card"></i> Kreditkarte</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span4">
				<label>Anbieter</label>
				<div class="value">'.$card->CardProvider.'</div>
			</div>
			<div class="span5">
				<label>Card Number</label>
				<div class="value">'.$helperCardNumber->set( $card->Alias )->render().'</div>
			</div>
			<div class="span3">
				<label>Gültig bis</label>
				<div class="value">'.$card->ExpirationDate.'</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span5">
				<label>Land</label>
				<div class="value">'.$card->Country.'</div>
			</div>
			<div class="span3">
				<label>Währung</label>
				<div class="value">'.$card->Currency.'</div>
			</div>
			<div class="span4">
				<label>Registriert</label>
				<div class="value">'.date( 'd.m.Y', $card->CreationDate ).'</div>
			</div>
		</div>
<!--		'.print_m( $card, NULL, NULL, TRUE ).'-->
<!--		<div class="buttonbar">
			<a href="'.$linkBack.'" class="btn"><b class="fa fa-arrow-left"></b> zurück</a>
			&nbsp;|&nbsp;
			'.$buttonPayIn.'
			'.$buttonPreAuth.'
			&nbsp;|&nbsp;
			'.$buttonPayOut.'
		</div>-->
	</div>
</div>';



$panelPayin		= $view->loadTemplateFile( 'manage/my/mangopay/card/view.payin.php' );
$panelRemove	= $view->loadTemplateFile( 'manage/my/mangopay/card/view.remove.php' );

return '
<h2><a class="muted" href="./manage/my/mangopay/card">Kreditkarte</a> '.$card->CardProvider.'</h2>
<div class="row-fluid">
	<div class="span6">
		'.$panelPayin.'
	</div>
	<div class="span6">
		'.$panelView.'
		'.$panelRemove.'
	</div>
</div>
'./*$panelTransactions.*/'
';
