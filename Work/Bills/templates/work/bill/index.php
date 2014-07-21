<?php

/*$model	= new Model_Bill( $this->env );
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

$statusClasses	= array(
	'error',
	'warning',
	'success',
);

$conditions	= array(
	'userId'	=> $userId,
//	'date'		=> '>'.$yearStart.$monthStart.'00',
//	'date'		=> '<'.$yearEnd.$monthEnd.'32',
);
$orders		= array( 'date' => 'ASC' );
$bills		= $model->getAll( $conditions, $orders );
*/
$iconIn		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-right', 'title' => 'an andere' ) );
$iconOut	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left', 'title' => 'von anderen' ) );

$table		= $view->renderTable( $bills );
$filter		= $this->loadTemplateFile( 'work/bill/index.filter.php' );

$w		= (object) $words['index'];
$tabs	= View_Work_Bill::renderTabs( $env );

$pagination	= new View_Helper_Pagination();
$pagination	= $pagination->render( './work/bill', $total, $filters->get( 'limit' ), $page );

return '
<!--<h2>'.$w->heading.'</h2>-->
'.$tabs.'
<div class="row-fluid">
	<div class="span3">
		'.$filter.'
	</div>
	<div class="span9">
		'.$table.'
		'.$pagination.'
		<div class="buttonbar">
			<a href="./work/bill/add" class="btn btn-success"><i class="icon-plus icon-white"></i>&nbsp;neue Rechnung</a>
		</div>
	</div>
</div>';
