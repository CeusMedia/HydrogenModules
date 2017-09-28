<?php

$buttonPayIn	= '<a href="./manage/my/mangopay/card/payin/'.$cardId.'" class="btn btn-small"><b class="fa fa-sign-in"></b> Einzahlung</a>';
$buttonPayOut	= '<a href="./manage/my/mangopay/card/payOut/'.$cardId.'" class="btn btn-small"><b class="fa fa-sign-out"></b> Auszahlung</a>';
$buttonPreAuth	= '<a href="./manage/my/mangopay/card/payin/preAuthorized/'.$cardId.'" class="btn btn-small"><b class="fa fa-sign-in"></b> Einzahlung mit Reservierung</a>';
$buttonDeactivate	= '<a href="./manage/my/mangopay/card/deactivate/'.$cardId.'" class="btn btn-small btn-danger"><b class="fa fa-remove"></b> entfernen</a>';
if( 0 && !$card->Active ){
	$buttonPayIn	= '<button type="button" class="btn btn-small" disabled="disabled"><b class="fa fa-sign-in"></b> Einzahlung</button>';
	$buttonPreAuth	= '<button type="button" class="btn btn-small" disabled="disabled"><b class="fa fa-sign-in"></b> Einzahlung mit Reservierung</a>';
	$buttonPayOut	= '<button type="button" class="btn btn-small" disabled="disabled"><b class="fa fa-sign-out"></b> Auszahlung</button>';
}

$linkBack	= './'.( $backwardTo ? $backwardTo : 'manage/my/mangopay/card' );

return '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>Credit Card</h3>
			<div class="content-panel-inner">
				'.print_m( $card, NULL, NULL, TRUE ).'
				<div class="buttonbar">
					<a href="'.$linkBack.'" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>
					&nbsp;|&nbsp;
					'.$buttonPayIn.'
					'.$buttonPreAuth.'
					&nbsp;|&nbsp;
					'.$buttonPayOut.'
					&nbsp;|&nbsp;
					'.$buttonDeactivate.'
				</div>
			</div>
		</div>
	</div>
	<div class="span6">
		'./*$panelTransactions.*/'
	</div>
</div>';
