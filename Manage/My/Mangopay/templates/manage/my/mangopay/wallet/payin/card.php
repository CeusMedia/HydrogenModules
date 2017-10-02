<?php
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
?>
