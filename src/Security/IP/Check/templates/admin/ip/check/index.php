<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var array<object> $entries */
/** @var int $total */
/** @var int $page */
/** @var int $pageSize */
/** @var int $pages */
/** @var string $filterIp */
/** @var string $filterDateFrom */
/** @var string $filterDateTo */
/** @var string $filterScoreMin */

$wf	= (object) $words['index'];

// Filter form
$filterRows	= [];
$filterRows[]	= HtmlTag::create( 'div', '
	<label for="filter_ip">'.$wf->filterIp.'</label>
	<input type="text" name="ip" id="filter_ip" class="span6" value="'.$filterIp.'" />
', [ 'class' => 'span3' ] );
$filterRows[]	= HtmlTag::create( 'div', '
	<label for="filter_dateFrom">'.$wf->filterDateFrom.'</label>
	<input type="date" name="dateFrom" id="filter_dateFrom" class="span6" value="'.$filterDateFrom.'" />
', [ 'class' => 'span3' ] );
$filterRows[]	= HtmlTag::create( 'div', '
	<label for="filter_dateTo">'.$wf->filterDateTo.'</label>
	<input type="date" name="dateTo" id="filter_dateTo" class="span6" value="'.$filterDateTo.'" />
', [ 'class' => 'span3' ] );
$filterRows[]	= HtmlTag::create( 'div', '
	<label for="filter_scoreMin">'.$wf->filterScoreMin.'</label>
	<input type="number" name="scoreMin" id="filter_scoreMin" class="span6" value="'.$filterScoreMin.'" min="-200" max="0" />
', [ 'class' => 'span3' ] );

$iconFilter	= new Icon( 'search' );
$iconReset	= new Icon( 'trash' );
$buttonFilter	= HtmlTag::create( 'button', $iconFilter . '&nbsp;'.$wf->buttonFilter, [
	'type'	=> 'submit',
	'class'	=> 'btn btn-primary',
] );
$buttonReset	= HtmlTag::create( 'a', $iconReset . '&nbsp;'.$wf->buttonReset, [
	'href'	=> './admin/ip/check/filter?reset=1',
	'class'	=> 'btn btn-default',
] );

$filterForm	= '
<form action="./admin/ip/check/filter" method="get" class="form-inline">
	<div class="row-fluid">
		'.$filterRows[0].'
		'.$filterRows[1].'
		'.$filterRows[2].'
		'.$filterRows[3].'
		<div class="span3">
			<label>&nbsp;</label>
			'.$buttonFilter.'
			'.$buttonReset.'
		</div>
	</div>
</form>';

// Table
$rows	= [];
foreach( $entries as $entry ){
	$reasons	= $entry->reasons ?? '';
	$reasons	= json_decode( $reasons, TRUE );
	$reasons	= is_array( $reasons ) ? implode( ', ', $reasons ) : $reasons;

	$detailLink	= HtmlTag::create( 'a', $entry->ip, [
		'href'	=> './admin/ip/check/view/'.$entry->id,
		'title'	=> $wf->titleDetail,
	] );

	$deleteLink	= HtmlTag::create( 'a', new Icon( 'trash' ), [
		'href'	=> './admin/ip/check/delete/'.$entry->id,
		'class'	=> 'text-danger',
		'title'	=> $wf->titleDelete,
		'onclick'	=> 'return confirm("'.$wf->confirmDelete.'");',
	] );

	$cells	= [
		HtmlTag::create( 'td', $entry->id ?? '' ),
		HtmlTag::create( 'td', $entry->createdAt ?? '' ),
		HtmlTag::create( 'td', $detailLink ),
		HtmlTag::create( 'td', $entry->ipVersion ?? '' ),
		HtmlTag::create( 'td', '<strong>'.$entry->score.'</strong>' ),
		HtmlTag::create( 'td', '<small>'.$reasons.'</small>' ),
		HtmlTag::create( 'td', $entry->requestMethod ?? '' ),
		HtmlTag::create( 'td', '<small>'.substr( $entry->requestUri ?? '', 0, 50 ).'</small>' ),
		HtmlTag::create( 'td', $deleteLink ),
	];
	$rows[]	= HtmlTag::create( 'tr', $cells );
}

if( empty( $rows ) ){
	$emptyRow	= HtmlTag::create( 'tr', HtmlTag::create( 'td', $wf->noEntries, [ 'colspan' => 9 ] ) );
	$rows[]	= $emptyRow;
}

$table	= HtmlTag::create( 'table', [
	HtmlElements::ColumnGroup( ['4%', '12%', '12%', '5%', '5%', '20%', '8%', '20%', '4%'] ),
	HtmlElements::TableHeads( [
		$wf->headId,
		$wf->headCreatedAt,
		$wf->headIp,
		$wf->headVersion,
		$wf->headScore,
		$wf->headReasons,
		$wf->headMethod,
		$wf->headUri,
		$wf->headAction,
	] ),
	implode( $rows ),
], [ 'class' => 'table table-condensed table-striped' ] );

// Pagination
$pagination	= '';
if( $pages > 1 ){
	$pagesList	= [];
	$start	= max( 1, $page - 3 );
	$end	= min( $pages, $page + 3 );
	for( $i = $start; $i <= $end; $i++ ){
		$class	= $i === $page ? 'active' : '';
		$pagesList[]	= HtmlTag::create( 'li', HtmlTag::create( 'a', $i, [ 'href' => '?page='.$i ] ), [ 'class' => $class ] );
	}
	$pagination	= HtmlTag::create( 'ul', implode( $pagesList ), [ 'class' => 'pagination' ] );
}

// Action buttons
$iconExport	= new Icon( 'download' );
$iconCleanup	= new Icon( 'broom' );
$buttonExportCsv	= HtmlTag::create( 'a', $iconExport . '&nbsp;CSV', [
	'href'	=> './admin/ip/check/export?format=csv',
	'class'	=> 'btn btn-default',
] );
$buttonExportJson	= HtmlTag::create( 'a', $iconExport . '&nbsp;JSON', [
	'href'	=> './admin/ip/check/export?format=json',
	'class'	=> 'btn btn-default',
] );
$buttonCleanup	= HtmlTag::create( 'a', $iconCleanup . '&nbsp;'.$wf->buttonCleanup, [
	'href'	=> './admin/ip/check/cleanup',
	'class'	=> 'btn btn-warning',
	'onclick'	=> 'return confirm("'.$wf->confirmCleanup.'");',
] );
$buttonDeleteAll	= HtmlTag::create( 'a', new Icon( 'trash' ) . '&nbsp;'.$wf->buttonDeleteAll, [
	'href'	=> './admin/ip/check/deleteAll',
	'class'	=> 'btn btn-danger',
	'onclick'	=> 'return confirm("'.$wf->confirmDeleteAll.'");',
] );

$actionButtons	= '
<div class="row-fluid">
	<div class="span6">
		'.$buttonExportCsv.'
		'.$buttonExportJson.'
	</div>
	<div class="span6 text-right">
		'.$buttonCleanup.'
		'.$buttonDeleteAll.'
	</div>
</div>';

return '
<h3>'.$wf->legend.'</h3>
<p class="text-muted">'.$wf->totalEntries.': '.$total.' ('.$page.'/'.$pages.')</p>
'.$filterForm.'
'.$actionButtons.'
'.$table.'
'.$pagination;
