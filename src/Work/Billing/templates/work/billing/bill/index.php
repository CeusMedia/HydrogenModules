<?php

use CeusMedia\Bootstrap\PageControl as BootstrapPageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var object $bill */
/** @var object[] $bills */
/** @var int $page */
/** @var int $pages */

$w		= (object) $env->getLanguage()->getSection( 'work/billing', 'bill-index' );

$wf		= (object) $env->getLanguage()->getSection( 'work/billing', 'filter' );

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$list	= HtmlTag::create( 'em', $w->noEntries, ['class' => 'muted'] );

$statuses	= [
	0	=> $w->statusWorking,
	1	=> $w->statusBooked,
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
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $number ),
			HtmlTag::create( 'td', $title, ['class' => 'cell-title autocut'] ),
			HtmlTag::create( 'td', number_format( $bill->amountNetto, 2, ',', '.' ).'&nbsp;&euro;', ['class' => 'cell-number'] ),
			HtmlTag::create( 'td', number_format( $bill->taxRate, 2, ',', '.' ).'%', ['class' => 'cell-number cell-bill-tax'] ),
			HtmlTag::create( 'td', $dateBooked, ['class' => 'cell-number'] ),
			HtmlTag::create( 'td', $status ),
		], ['class' => $bill->status > 0 ? 'success' : 'warning'] );
	}
	$colgroup	= HtmlElements::ColumnGroup( [
		'150',
		'',
		'90',
		'70',
		'70',
		'90',
	] );
	$thead	= HtmlTag::create( 'thead', HtmlTag::create( 'tr', [
		HtmlTag::create( 'th', $w->headNumber ),
		HtmlTag::create( 'th', $w->headTitle ),
		HtmlTag::create( 'th', $w->headAmount, ['class' => 'cell-number'] ),
		HtmlTag::create( 'th', '<small>'.$w->headTax.'</small>', ['class' => 'cell-number'] ),
		HtmlTag::create( 'th', '<small>'.$w->headBooked.'</small>', ['class' => 'cell-number'] ),
		HtmlTag::create( 'th', '<small>'.$w->headStatus.'</small>' ),
	] ) );
	$tfoot	= HtmlTag::create( 'tfoot', HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', $w->total, ['colspan' => 2] ),
		HtmlTag::create( 'td', number_format( $totalAmount, 2, ',', '.' ).'&nbsp;&euro;', ['class' => 'cell-number'] ),
		HtmlTag::create( 'td', '' ),
		HtmlTag::create( 'td', '' ),
		HtmlTag::create( 'td', '' ),
	] ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody.$tfoot, ['class' => 'table table-fixed'] );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' '.$w->buttonAdd, [
	'href'	=> './work/billing/bill/add',
	'class'	=> 'btn btn-success',
] );

$optStatus	= [
	''	=> $wf->optAll,
	'0'	=> $w->statusWorking,
	'1'	=> $w->statusBooked,
];
$optStatus	= HtmlElements::Options( $optStatus, $filterStatus );

$optYear	= [
	''	=> $wf->optAll,
];
$optYear[date( "Y" )]	= date( "Y" );
$optYear[date( "Y" )-1]	= date( "Y" )-1;
$optYear[date( "Y" )-2]	= date( "Y" )-2;
$optYear	= HtmlElements::Options( $optYear, $filterYear );

$optMonth	= [
	''		=> $wf->optAll,
	'01'	=> $wf->month01,
	'02'	=> $wf->month02,
	'03'	=> $wf->month03,
	'04'	=> $wf->month04,
	'05'	=> $wf->month05,
	'06'	=> $wf->month06,
	'07'	=> $wf->month07,
	'08'	=> $wf->month08,
	'09'	=> $wf->month09,
	'10'	=> $wf->month10,
	'11'	=> $wf->month11,
	'12'	=> $wf->month12,
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
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./work/billing/bill/filter" class="form-list-filter" method="post">
			<div class="row-fluid">
				<div class="span2">
					<label for="input_number">'.$w->labelNumber.'</label>
					<input type="text" name="number" id="input_number" class="span12" value="'.htmlentities( $filterNumber, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $filterTitle, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12" onchange="this.form.submit()">'.$optStatus.'</select>
				</div>
				<div class="span2">
					<label for="input_year">'.$wf->labelYear.'</label>
					<select name="year" id="input_year" class="span12" onchange="this.form.submit()">'.$optYear.'</select>
				</div>
				<div class="span2">
					<label for="input_month">'.$wf->labelMonth.'</label>
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
