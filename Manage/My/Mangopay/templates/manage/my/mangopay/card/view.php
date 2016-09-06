<?php

$buttonPayIn	= '<a href="./manage/my/mangopay/card/payin/'.$cardId.'" class="btn btn-small"><b class="fa fa-sign-in"></b> einzahlen</a>';
$buttonPayOut	= '<a href="./manage/my/mangopay/card/payout/'.$cardId.'" class="btn btn-small"><b class="fa fa-sign-out"></b> auszahlen</a>';
if( !$card->Active )
	$buttonPayIn	= '<button type="button" class="btn btn-small" disabled="disabled"><b class="fa fa-sign-in"></b> einzahlen</button>';
if( !$card->Active )
	$buttonPayOut	= '<button type="button" class="btn btn-small" disabled="disabled"><b class="fa fa-sign-out"></b> auszahlen</button>';

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
					'.$buttonPayIn.'
					'.$buttonPayOut.'
				</div>
			</div>
		</div>
	</div>
	<div class="span6">
		'./*$panelTransactions.*/'
	</div>
</div>';
