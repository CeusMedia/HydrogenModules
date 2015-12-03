<?php

$list	= '<div class="muted"><em><small>No exceptions logged.</small></em></div>';
if( $exceptions ){
	$list	= array();
	foreach( $exceptions as $nr => $exception ){

		$link	= UI_HTML_Tag::create( 'a', $exception->message, array( 'href' => './system/log/view/'.$exception->id ) );
		$date	= date( 'Y.m.d', $exception->timestamp );
		$time	= date( 'H:i:s', $exception->timestamp );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', $date.'&nbsp;<small class="muted">'.$time.'</small>' ),
		) );
	}
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $tbody, array( 'class' => 'table table-striped table-condensed' ) );
}

$pagination	= new \CeusMedia\Bootstrap\PageControl( './system/log', $page, ceil( $total / $limit ) );
$pagination	= $pagination->render();

$panelList	= '
		<div class="content-panel">
			<h3>Exceptions</h3>
			<div class="content-panel-inner">
				'.$list.'
				'.$pagination.'
			</div>
		</div>
';
return $panelList;

return '
<div class="row-fluid">
	<div class="span3">
		<div class="content-panel">
			<h3>Filter</h3>
			<div class="content-panel-inner">
			</div>
		</div>
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>
';
