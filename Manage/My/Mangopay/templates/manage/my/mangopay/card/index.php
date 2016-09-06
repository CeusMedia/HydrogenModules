<?php

$rows		= array();
foreach( $cards as $item ){
//	if( !$item->Active )
//		continue;
	$link	= UI_HTML_Tag::create( 'a', $item->Id, array( 'href' => './manage/my/mangopay/card/view/'.$item->Id ) );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create(' td', $link, array( 'class' => 'cell-card-id' ) ),
		UI_HTML_Tag::create(' td', $item->CardProvider, array( 'class' => 'cell-card-provider' ) ),
		UI_HTML_Tag::create(' td', $item->Alias, array( 'class' => 'cell-card-title' ) ),
		UI_HTML_Tag::create(' td', $item->Currency, array( 'class' => 'cell-card-currency' ) ),
		UI_HTML_Tag::create(' td', $item->Active ? 'aktiv' : 'inaktiv', array( 'class' => 'cell-card-status' ) ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( "60", "60", "", "90", "90" );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'ID', 'Provider', 'Card Number <small class="muted">(anonymisiert)</small>', 'Currency', 'Status' ) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );

$panelCards	= '
<div class="content-panel">
	<h3>Credit Cards <small class="muted"></small></h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			<a href="./manage/my/mangopay/card/add" class="btn btn-success"><b class="fa fa-plus"></b> add</a>
		</div>
	</div>
</div>';

return $panelCards;
