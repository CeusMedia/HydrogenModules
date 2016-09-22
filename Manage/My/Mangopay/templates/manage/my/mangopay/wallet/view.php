<?php

$list	= new View_Helper_Accordion( 'user-transations' );
$list->setSingleOpen( TRUE );
foreach( $transactions as $item ){
	$id			= UI_HTML_Tag::create( 'small', $item->Id.':', array( 'class' => 'muted' ) );
	$title		= $id.'&nbsp;'.$this->formatMoney( $item->DebitedFunds );
	$content	= ltrim( print_m( $item, NULL, NULL, TRUE ), '<br/>' );
	$list->add( 'user-transation-'.$item->Id, $title, $content );
}
$panelTransactions	= '
<div class="content-panel">
	<h3>Transactions</h3>
	<div class="content-panel-inner">
		'.$list->render().'
	</div>
</div>';

$linkBack	= './'.( $backwardTo ? $backwardTo : 'manage/my/mangopay/wallet' );

$buttonCancel	= '<a href="'.$linkBack.'" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>';
$buttonPayIn	= '<a href="./manage/my/mangopay/wallet/payin/'.$walletId.'" class="btn btn-primary not-btn-small"><b class="fa fa-sign-in"></b> Einzahlung</a>';
$buttonPayOut	= '<a href="./manage/my/mangopay/wallet/payout/'.$walletId.'" class="btn btn-primary not-btn-small"><b class="fa fa-sign-out"></b> Auszahlung</a>';

return '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>Wallet</h3>
			<div class="content-panel-inner">
				'.print_m( $wallet, NULL, NULL, TRUE ).'
				<div class="buttonbar">
					'.$buttonCancel.'
					'.$buttonPayIn.'
					'.$buttonPayOut.'
				</div>
			</div>
		</div>
	</div>
	<div class="span6">
		'.$panelTransactions.'
	</div>
</div>';
