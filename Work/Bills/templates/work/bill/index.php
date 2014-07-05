<?php

$model	= new Model_Bill( $this->env );
$year	= date( 'Y' );
$month	= date( 'm' );
$yearStart	= $year;
$monthStart	= $month - 1;
$yearEnd	= $year;
$monthEnd	= $month + 2;
if( $monthStart < 1 ){
	$yearStart	= $year - 1;
	$monthStart = 12;
}
if( $monthEnd > 12 ){
	$yearEnd	= $year + 1;
	$monthEnd	-= 12;
}

$conditions	= array(
//	'userId'	=> $this->env->getSession()->get( 'userId' ),
//	'date'		=> '>'.$yearStart.$monthStart.'00',
//	'date'		=> '<'.$yearEnd.$monthEnd.'32',
);
$orders		= array( 'date' => 'ASC' );
$bills		= $model->getAll( $conditions, $orders );

$iconIn		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-right', 'title' => 'an andere' ) );
$iconOut	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left', 'title' => 'von anderen' ) );

$table		= '<div><em class="muted">Keine Einträge vorhanden.</em></div><br/>';
if( $bills ){
	$rows	= array();
	foreach( $bills as $bill ){
		$date	= strtotime( substr( $bill->date, 0, 4 ).'-'.substr( $bill->date, 4, 2).'-'.substr( $bill->date, 6, 2 ) );
		$label	= ( $bill->type ? $iconOut : $iconIn ) . '&nbsp;'.$bill->title;
		$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => './work/bill/edit/'.$bill->billId ) );
		$price	= number_format( $bill->price, 2, ',', '' ).'&nbsp;&euro;';
		$price	= $bill->type ? '<span class="negative">-'.$price.'</span>' : '<span class="positive">+'.$price.'</span>';
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link, array( 'class' => 'title' ) ),
			UI_HTML_Tag::create( 'td', $price ),
			UI_HTML_Tag::create( 'td', $words['states'][$bill->status] ),
			UI_HTML_Tag::create( 'td', date( 'd.m.Y', $date ) ),
		), array( 'class' => 'bill-type-'.$bill->type.' '.( $bill->status ? 'success' : 'warning' ) ) );
	}
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Title',
		'Betrag',
		'Zustand',
		'Fälligkeit',
	) ) );
	$colgroup	= UI_HTML_Elements::ColumnGroup( '40%', '20%', '20%', '20%' );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table' ) );
}

$filter		= $this->loadTemplateFile( 'work/bill/index.filter.php' );

$w	= (object) $words['index'];
$tabs	= View_Work_Bill::renderTabs( $env );

return '
<h2>'.$w->heading.'</h2>
'.$tabs.'
<div class="row-fluid">
	<div class="span3">
		'.$filter.'
	</div>
	<div class="span9">
		'.$table.'
		<a href="./work/bill/add" class="btn btn-success"><i class="icon-plus icon-white"></i>&nbsp;neue Rechnung</a>
		|
		<a href="./work/bill/graph" class="btn btn-info"><i class="icon-signal icon-white"></i>&nbsp;Prognose-Graph</a>
	</div>
</div>';
