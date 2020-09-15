<?php
$w	= (object) $words['index-new'];

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.' '.$w->buttonAdd, array( 'href' => './work/time/add', 'class' => 'btn btn-small btn-success' ) );

$helperShortList	= new View_Helper_Work_Time_ShortList( $env );
$helperShortList->setWorkerId( $userId );
$helperShortList->setStatus( array( 0 ) );
$helperShortList->setButtons( array( /*'mission-view'*/'start', 'stop', 'edit' ) );

$listNew	= $helperShortList->render();
if( !$listNew )
	$listNew	= '<div class="alert alert-info"><em class="muted">'.$w->empty.'</em></div>';

return '
<div class="content-panel content-panel-list">
	<h3>'.$w->heading.'</h4>
	<div class="content-panel-inner">
		'.$listNew.'
<!--		<div class="buttonbar">
			'.$buttonAdd.'
		</div>-->
	</div>
</div>';
?>
