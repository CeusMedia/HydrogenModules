<?php
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconUser		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user-o' ) );

$list	= UI_HTML_Tag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) );

if( $persons ){
	$list	= array();
	$totalPayout	= 0;
	$totalBalance	= 0;
	foreach( $persons as $person ){
		$link	= UI_HTML_Tag::create( 'a', $iconUser.'&nbsp;'.$person->firstname.'&nbsp;'.$person->surname, array( 'href' => './work/billing/person/edit/'.$person->personId ) );
		$payout	= 0;
		foreach( $person->payouts as $item )
			$payout	+= $item->amount;
		$totalPayout	+= $payout;
		$totalBalance	+= $person->balance;
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link, array ('class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', number_format( $payout, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
			UI_HTML_Tag::create( 'td', number_format( $person->balance, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
		'',
		'120',
		'100',
	) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Person' ),
		UI_HTML_Tag::create( 'th', 'Auszahlungen' ),
		UI_HTML_Tag::create( 'th', 'Balance', array( 'class' => 'cell-number' ) ),
	) ) );
	$tfoot	= UI_HTML_Tag::create( 'tfoot', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Gesamt' ),
		UI_HTML_Tag::create( 'th', number_format( $totalPayout, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
		UI_HTML_Tag::create( 'th', number_format( $totalBalance, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody.$tfoot, array( 'class' => 'table table-fixed' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.' neue Person', array(
	'href'	=> './work/billing/person/add',
	'class'	=> 'btn btn-success',
) );

return '
<div class="content-panel">
	<h3>Personen</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
