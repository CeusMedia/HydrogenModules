<?php

$list	= '<div class="alert alert-info">Keine Backups vorhanden.</div>';
if( $backups ){
	$rows	= array();
	foreach( $backups as $backup ){
		$rows[]	= U_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $backup->backupId ),
			UI_HTML_Tag::create( 'td', $backup->createdAt ),
		) );
	}
	$thead	= UI_HTML_Tag::create( 'thead', '' );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$list	= UI_HTML_Tag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table table-striped' ) );
}

$panelList	= '
<div class="content-panel">
	<h3>Backups</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			<a href="./admin/backup/add" class="btn btn-primary">neues Backup</a>
		</div>
	</div>
</div>';

$panelFilter	= '
<div class="content-panel">
	<h3>Backups</h3>
	<div class="content-panel-inner">
		<p>Not implemented, yet.</p>
	</div>
</div>';

return $panelList;
