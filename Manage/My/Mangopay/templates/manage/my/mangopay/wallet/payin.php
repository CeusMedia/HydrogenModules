<?php

if( $type == 'bankwire' ){
	if( isset( $payin ) ){
		return '
<div class="content-panel">
	<h3>Pay In</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/wallet/payIn/'.$walletId.'/bankwire" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_amount">Amount</label>
					<input type="text" name="amount" id="input_amount"/>
				</div>
				<div class="span6">
					<label for="input_currency">Currency</label>
					<input type="text" name="currency" id="input_currency" value="EUR"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" value="bankwire"><b class="fa fa-check"></b> Überweisung vorbereiten</button>
			<div>
		</form>
	</div>
</div>';

	}
	return '
<div class="content-panel">
	<h3>Pay In</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/wallet/payIn/'.$walletId.'/bankwire" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_amount">Amount</label>
					<input type="text" name="amount" id="input_amount"/>
				</div>
				<div class="span6">
					<label for="input_currency">Currency</label>
					<input type="text" name="currency" id="input_currency" value="EUR"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" value="bankwire"><b class="fa fa-check"></b> Überweisung vorbereiten</button>
			<div>
		</form>
	</div>
</div>';
}
else if( $type == 'card' ){
	$rows		= array();
	foreach( $cards as $item ){
		if( !$item->Active )
			continue;
		$button	= UI_HTML_Tag::create( 'a', 'use this!', array( 'class' => 'btn btn-small btn-primary', 'href' => './manage/my/mangopay/wallet/payIn/'.$walletId.'/card?cardId='.$item->Id ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create(' td', $item->CardProvider, array( 'class' => 'cell-card-provider' ) ),
			UI_HTML_Tag::create(' td', $item->Alias, array( 'class' => 'cell-card-title' ) ),
			UI_HTML_Tag::create(' td', $button, array( 'class' => 'cell-actions' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "60", "", "90", "120" );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Provider', 'Card Number <small class="muted">(anonymisiert)</small>', 'Aktion' ) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );

	$panelCards	= '
	<div class="content-panel">
		<h4>Credit Cards</h4>
		<div class="content-panel-inner">
			'.$table.'
		</div>
	</div>';
	return $panelCards;
}
else{

}


return '
<div class="content-panel">
	<h3>Pay In</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<p>
				...
			</p>
			<div class="buttonbar">
				<a href="./manage/my/mangopay/wallet/view/'.$walletId.'" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>
				<a href="./manage/my/mangopay/wallet/payIn/'.$walletId.'/bankwire" class="btn btn-primary"><b class="fa fa-arrow-right"></b> Überweisung</a>
				<a href="./manage/my/mangopay/wallet/payIn/'.$walletId.'/card" class="btn btn-primary"><b class="fa fa-credit-card"></b> Kreditkarse</a>
				<a href="./manage/my/mangopay/wallet/payIn/'.$walletId.'/directdebit" class="btn btn-primary"><b class="fa fa-money"></b> Lastschrift</a>
			</div>
		</div>
	</div>
</div>';
?>
