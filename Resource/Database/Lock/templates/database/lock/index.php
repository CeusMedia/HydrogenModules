<?php
$table	= '<div class="info empty"><em><small>Keine Sperren gefunden.</small></em></div>';
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

if( $locks ){
	$list	= array();
	foreach( $locks as $lock ){
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.' entfernen', array(
			'href'		=> './database/lock/unlock/'.$lock->lockId,
			'class'		=> 'btn btn-mini btn-danger'
		) );
		$moduleTitle	= $lock->module ? $lock->module : '<em><small class="muted">unbekannt</small></em>';
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $lock->user->username, array( 'class' => 'lock-user' ) ),
			UI_HTML_Tag::create( 'td', $moduleTitle, array( 'class' => 'lock-module' ) ),
			UI_HTML_Tag::create( 'td', $lock->title, array( 'class' => 'lock-title' ) ),
			UI_HTML_Tag::create( 'td', date( 'Y-m-d H:i:s', $lock->timestamp ), array( 'class' => 'lock-date' ) ),
			UI_HTML_Tag::create( 'td', $buttonRemove, array( 'class' => 'lock-actions' ) ),
		) );
	}
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Benutzer', 'Modul', 'Titel <small class="muted">(oder Subjekt)</small>', 'Datum', '' ) ) );
	$colgroup	= UI_HTML_Elements::ColumnGroup( '150px', '180px', '', '160px', '120px' );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-not-condensed' ) );
}

return '
<div class="content-panel">
	<h3><span class="muted">Datenbank: </span>Sperren</h3>
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>
';
?>
