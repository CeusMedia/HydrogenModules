<?php

use CeusMedia\Bootstrap\PageControl as BootstrapPageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var object $bill */
/** @var object[] $bills */
/** @var int $page */
/** @var int $pages */

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$list	= HtmlTag::create( 'em', 'Keine gefunden.', ['class' => 'muted'] );

$statuses	= [
	0	=> 'in Arbeit',
	1	=> 'gebucht',
];

if( $bills ){
	$list	= [];
	$totalAmount	= 0;
	foreach( $bills as $bill ){
		$totalAmount	+= $bill->amountNetto;
		$number	= HtmlTag::create( 'a', $bill->number, ['href' => './work/billing/bill/edit/'.$bill->billId] );
		$title	= HtmlTag::create( 'a', $bill->title, ['href' => './work/billing/bill/edit/'.$bill->billId] );
		$dateBooked	= '-';
		if( $bill->dateBooked != "0000-00-00" ){
			$year		= HtmlTag::create( 'small', date( 'y', strtotime( $bill->dateBooked ) ), ['class' => 'muted'] );
			$dateBooked	= date( 'd.m.', strtotime( $bill->dateBooked ) ).$year;
		}
		$status	= HtmlTag::create( 'small', $statuses[$bill->status] );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $number ),
			HtmlTag::create( 'td', $title, ['class' => 'cell-title autocut'] ),
			HtmlTag::create( 'td', number_format( $bill->amountNetto, 2, ',', '.' ).'&nbsp;&euro;', ['class' => 'cell-number'] ),
			HtmlTag::create( 'td', number_format( $bill->taxRate, 2, ',', '.' ).'%', ['class' => 'cell-number cell-bill-tax'] ),
			HtmlTag::create( 'td', $dateBooked, ['class' => 'cell-number'] ),
			HtmlTag::create( 'td', $status ),
		), ['class' => $bill->status > 0 ? 'success' : 'warning'] );
	}
	$colgroup	= HtmlElements::ColumnGroup( [
		'150',
		'',
		'90',
		'70',
		'70',
		'90',
	] );
	$thead	= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', 'Nr' ),
		HtmlTag::create( 'th', 'Bezug' ),
		HtmlTag::create( 'th', 'Betrag', ['class' => 'cell-number'] ),
		HtmlTag::create( 'th', '<small>MwSt</small>', ['class' => 'cell-number'] ),
		HtmlTag::create( 'th', '<small>gebucht</small>', ['class' => 'cell-number'] ),
		HtmlTag::create( 'th', '<small>Zustand</small>' ),
	) ) );
	$tfoot	= HtmlTag::create( 'tfoot', HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', 'Gesamt', ['colspan' => 2] ),
		HtmlTag::create( 'td', number_format( $totalAmount, 2, ',', '.' ).'&nbsp;&euro;', ['class' => 'cell-number'] ),
		HtmlTag::create( 'td', '' ),
		HtmlTag::create( 'td', '' ),
		HtmlTag::create( 'td', '' ),
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody.$tfoot, ['class' => 'table table-fixed'] );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' neue Rechnung', [
	'href'	=> './work/billing/bill/add',
	'class'	=> 'btn btn-success',
] );

$optStatus	= [
	''	=> '- alle -',
	'0'	=> 'in Arbeit',
	'1'	=> 'gebucht',
];
$optStatus	= HtmlElements::Options( $optStatus, $filterStatus );

$optYear	= [
	''	=> '- alle -',
];
$optYear[date( "Y" )]	= date( "Y" );
$optYear[date( "Y" )-1]	= date( "Y" )-1;
$optYear[date( "Y" )-2]	= date( "Y" )-2;
$optYear	= HtmlElements::Options( $optYear, $filterYear );

$optMonth	= [
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
];
$optMonth	= HtmlElements::Options( $optMonth, $filterMonth );

$pagination	= new BootstrapPageControl( './work/billing/bill', $page, $pages );

$iconFilter	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] );

$buttonFilter	= HtmlTag::create( 'button', $iconFilter, [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-small btn-info',
	'style'	=> 'display: none'
] );

return '
<div class="content-panel">
	<h3>Rechnungen</h3>
	<div class="content-panel-inner">
		<form action="./work/billing/bill/filter" class="form-list-filter" method="post">
			<div class="row-fluid">
				<div class="span2">
					<label for="input_number">Nummer</label>
					<input type="text" name="number" id="input_number" class="span12" value="'.htmlentities( $filterNumber, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $filterTitle, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
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
			'.$buttonFilter.'
		</form>
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
			'.$pagination.'
		</div>
	</div>
</div>
<style>
table td.cell-bill-tax {
	}
</style>';
