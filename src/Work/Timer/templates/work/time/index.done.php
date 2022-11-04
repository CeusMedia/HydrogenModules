<?php
$w	= (object) $words['index-done'];

$helperShortList	= new View_Helper_Work_Time_ShortList( $env );
$helperShortList->setWorkerId( $userId );
$helperShortList->setStatus( [3] );
$helperShortList->setOrders( ['modifiedAt' => 'DESC'] );
$helperShortList->setLimits( 10 );
$helperShortList->setButtons( ['start', 'stop', 'edit'] );

$listDone	= $helperShortList->render();
if( !$listDone )
	$listDone	= '<div class="alert alert-info"><em class="muted">'.$w->empty.'</em></div>';

return '
<div class="content-panel content-panel-list">
	<h3>'.$w->heading.'</h4>
	<div class="content-panel-inner">
		'.$listDone.'
	</div>
</div>';
