<?php

$buttonPayIn	= '<a href="./manage/my/mangopay/bank/payIn/'.$bankAccountId.'" class="btn btn-small"><b class="fa fa-sign-in"></b> Einzahlung</a>';
$buttonPayOut	= '<a href="./manage/my/mangopay/bank/payOut/'.$bankAccountId.'" class="btn btn-small"><b class="fa fa-sign-out"></b> Auszahlung</a>';
if( 0 && !$bankAccount->Active ){
	$buttonPayIn	= '<button type="button" class="btn btn-small" disabled="disabled"><b class="fa fa-sign-in"></b> Einzahlung</button>';
	$buttonPayOut	= '<button type="button" class="btn btn-small" disabled="disabled"><b class="fa fa-sign-out"></b> Auszahlung</button>';
}

$linkBack	= './'.( $backwardTo ? $backwardTo : 'manage/my/mangopay/bank' );

return '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>Bank Account</h3>
			<div class="content-panel-inner">
				'.print_m( $bankAccount, NULL, NULL, TRUE ).'
				<div class="buttonbar">
					<a href="'.$linkBack.'" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>
					&nbsp;|&nbsp;
					'.$buttonPayIn.'
					&nbsp;|&nbsp;
					'.$buttonPayOut.'
				</div>
			</div>
		</div>
	</div>
	<div class="span6">
		'./*$panelTransactions.*/'
	</div>
</div>';
