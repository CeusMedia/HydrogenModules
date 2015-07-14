<?php

$panelSearch	= '
<div class="content-panel">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		...
	</div>
</div>';


$list		= '<div><em><small class="muted">Noch keine vorhanden.</small></em></div>';
if( $users ){
	$list	= array();
	foreach( $users as $user ){
		$link	= UI_HTML_Tag::create( 'a', $user->username, array(
			'href'	=> './member/view/'.$user->userId,
		) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
		) );
	}
	$list	= UI_HTML_Tag::create( 'table', $list, array( 'class' => 'table table-striped' ) );
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
