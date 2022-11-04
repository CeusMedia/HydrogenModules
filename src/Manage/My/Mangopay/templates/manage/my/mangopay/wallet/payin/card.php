<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$rows		= [];
foreach( $cards as $item ){
	if( !$item->Active )
		continue;
	$button	= HtmlTag::create( 'a', 'use this!', ['class' => 'btn btn-small btn-primary', 'href' => './manage/my/mangopay/wallet/payIn/'.$walletId.'/card?cardId='.$item->Id] );
	$rows[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create(' td', $item->CardProvider, ['class' => 'cell-card-provider'] ),
		HtmlTag::create(' td', $item->Alias, ['class' => 'cell-card-title'] ),
		HtmlTag::create(' td', $button, ['class' => 'cell-actions'] ),
	) );
}
$colgroup	= HtmlElements::ColumnGroup( "60", "", "90", "120" );
$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( array( 'Provider', 'Card Number <small class="muted">(anonymisiert)</small>', 'Aktion' ) ) );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );

$panelCards	= '
<div class="content-panel">
	<h4>Credit Cards</h4>
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';
return $panelCards;
?>
