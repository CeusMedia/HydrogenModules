<?php

$panelSearch	= '
<div class="content-panel">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		...
	</div>
</div>';


$list	= '<div><em><small class="muted">Noch keine vorhanden.</small></em></div>';
if( $relations ){
	$list	= array();
	foreach( $relations as $relation ){
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $relation->user->username ),
		) );
	}
	$list	= UI_HTML_Tag::create( 'table', $list, array( 'class' => 'table' ) );
}
$panelList	= '
<div class="content-panel">
	<h3>Bekannte Mitglieder</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';

return '
<h2>Mitglieder</h2>
<div class="row-fluid">
	<div class="span3">
		'.$panelSearch.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>';
