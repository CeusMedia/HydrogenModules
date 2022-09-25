<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconUser		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user-o' ) );

$list	= HtmlTag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) );

if( $persons ){
	$list	= [];
	$totalPayout	= 0;
	$totalBalance	= 0;
	foreach( $persons as $person ){
		$link	= HtmlTag::create( 'a', $iconUser.'&nbsp;'.$person->firstname.'&nbsp;'.$person->surname, array( 'href' => './work/billing/person/edit/'.$person->personId ) );
		$payout	= 0;
		foreach( $person->payouts as $item )
			$payout	+= $item->amount;
		$totalPayout	+= $payout;
		$totalBalance	+= $person->balance;
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link, array ('class' => 'autocut' ) ),
			HtmlTag::create( 'td', number_format( $payout, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
			HtmlTag::create( 'td', number_format( $person->balance, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
		'',
		'120',
		'100',
	) );
	$thead	= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', 'Person' ),
		HtmlTag::create( 'th', 'Auszahlungen' ),
		HtmlTag::create( 'th', 'Balance', array( 'class' => 'cell-number' ) ),
	) ) );
	$tfoot	= HtmlTag::create( 'tfoot', HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', 'Gesamt' ),
		HtmlTag::create( 'th', number_format( $totalPayout, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
		HtmlTag::create( 'th', number_format( $totalBalance, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody.$tfoot, array( 'class' => 'table table-fixed' ) );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' neue Person', array(
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
