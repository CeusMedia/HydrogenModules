<?php
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var string $userId */

$w	= (object) $words['index-paused'];

$helperShortList	= new View_Helper_Work_Time_ShortList( $env );
$helperShortList->setWorkerId( $userId );
$helperShortList->setStatus( [2] );
$helperShortList->setOrders( ['modifiedAt' => 'DESC'] );
$helperShortList->setLimits( 10 );
$helperShortList->setButtons( ['start', 'stop', 'edit'] );

$listActive	= $helperShortList->render();
if( !$listActive )
	$listActive	= '<div class="alert alert-info"><em class="muted">'.$w->empty.'</em></div>';

return '
<div class="content-panel content-panel-list">
	<h3>'.$w->heading.'</h4>
	<div class="content-panel-inner">
		'.$listActive.'
	</div>
</div>';
