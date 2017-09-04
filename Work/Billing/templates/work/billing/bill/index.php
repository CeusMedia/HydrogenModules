<?php

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$list	= UI_HTML_Tag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) );

$statuses	= array(
	0	=> 'in Arbeit',
	1	=> 'gebucht',
);

if( $bills ){
	$list	= array();
	$totalAmount	= 0;
	foreach( $bills as $bill ){
		$totalAmount	+= $bill->amountNetto;
		$number	= UI_HTML_Tag::create( 'a', $bill->number, array( 'href' => './work/billing/bill/edit/'.$bill->billId ) );
		$title	= UI_HTML_Tag::create( 'a', $bill->title, array( 'href' => './work/billing/bill/edit/'.$bill->billId ) );
		$dateBooked	= '-';
		if( $bill->dateBooked != "0000-00-00" ){
			$year		= UI_HTML_Tag::create( 'small', date( 'y', strtotime( $bill->dateBooked ) ), array( 'class' => 'muted' ) );
			$dateBooked	= date( 'd.m.', strtotime( $bill->dateBooked ) ).$year;
		}
		$status	= UI_HTML_Tag::create( 'small', $statuses[$bill->status] );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $number ),
			UI_HTML_Tag::create( 'td', $title, array( 'class' => 'cell-title autocut' ) ),
			UI_HTML_Tag::create( 'td', number_format( $bill->amountNetto, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
			UI_HTML_Tag::create( 'td', number_format( $bill->taxRate, 2, ',', '.' ).'%', array( 'class' => 'cell-number cell-bill-tax' ) ),
			UI_HTML_Tag::create( 'td', $dateBooked, array( 'class' => 'cell-number' ) ),
			UI_HTML_Tag::create( 'td', $status ),
		), array( 'class' => $bill->status > 0 ? 'success' : 'warning' ) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
		'120',
		'',
		'90',
		'70',
		'70',
		'90',
	) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Nr' ),
		UI_HTML_Tag::create( 'th', 'Bezug' ),
		UI_HTML_Tag::create( 'th', 'Betrag', array( 'class' => 'cell-number' ) ),
		UI_HTML_Tag::create( 'th', '<small>MwSt</small>', array( 'class' => 'cell-number' ) ),
		UI_HTML_Tag::create( 'th', '<small>gebucht</small>', array( 'class' => 'cell-number' ) ),
		UI_HTML_Tag::create( 'th', '<small>Zustand</small>' ),
	) ) );
	$tfoot	= UI_HTML_Tag::create( 'tfoot', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', 'Gesamt', array( 'colspan' => 2 ) ),
		UI_HTML_Tag::create( 'td', number_format( $totalAmount, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
		UI_HTML_Tag::create( 'td', '' ),
		UI_HTML_Tag::create( 'td', '' ),
		UI_HTML_Tag::create( 'td', '' ),
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody.$tfoot, array( 'class' => 'table table-fixed' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.' neue Rechnungen', array(
	'href'	=> './work/billing/bill/add',
	'class'	=> 'btn btn-success',
) );

$optStatus	= array(
	''	=> '- alle -',
	'0'	=> 'in Arbeit',
	'1'	=> 'gebucht',
);
$optStatus	= UI_HTML_Elements::Options( $optStatus, $filterStatus );

$optYear	= array(
	''	=> '- alle -',
);
$optYear[date( "Y" )]	= date( "Y" );
$optYear[date( "Y" )-1]	= date( "Y" )-1;
$optYear[date( "Y" )-2]	= date( "Y" )-2;
$optYear	= UI_HTML_Elements::Options( $optYear, $filterYear );

$optMonth	= array(
	''		=> '- alle -',
	'01'	=> 'Januar',
	'02'	=> 'Februar',
	'03'	=> 'März',
	'04'	=> 'April',
	'05'	=> 'Mai',
	'06'	=> 'Juni',
	'07'	=> 'Juli',
	'08'	=> 'August',
	'09'	=> 'September',
	'10'	=> 'Oktober',
	'11'	=> 'November',
	'12'	=> 'Dezember',
);
$optMonth	= UI_HTML_Elements::Options( $optMonth, $filterMonth );

return '
<div class="content-panel">
	<h3>Rechnungen</h3>
	<div class="content-panel-inner">
		<form action="./work/billing/bill/filter" method="post">
			<div class="row-fluid">
				<div class="span2">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12" onchange="this.form.submit()">'.$optStatus.'</select>
				</div>
				<div class="span2">
					<label for="input_year">Jahr</label>
					<select name="year" id="input_year" class="span12" onchange="this.form.submit()">'.$optYear.'</select>
				</div>
				<div class="span2">
					<label for="input_month">Monat</label>
					<select name="month" id="input_month" class="span12" onchange="this.form.submit()">'.$optMonth.'</select>
				</div>
			</div>
		</form>
		<hr/>
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>
<style>
table td.cell-bill-tax {
}
</style>';
