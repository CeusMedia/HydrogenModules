<?php

$w		= (object) $words['index'];

$table	= '<div class="info empty"><em><small>'.$w->noEntries.'</small></em></div>';
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

if( $locks ){
	$list	= array();
	foreach( $locks as $lock ){
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, array(
			'href'		=> './database/lock/unlock/'.$lock->lockId,
			'class'		=> 'btn btn-mini btn-danger'
		) );
		$moduleTitle	= $lock->module ? $lock->module : '<em><small class="muted">'.$w->moduleUnknown.'</small></em>';
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $lock->user->username, array( 'class' => 'lock-user' ) ),
			UI_HTML_Tag::create( 'td', $moduleTitle, array( 'class' => 'lock-module' ) ),
			UI_HTML_Tag::create( 'td', $lock->title, array( 'class' => 'lock-title' ) ),
			UI_HTML_Tag::create( 'td', date( 'Y-m-d H:i:s', $lock->timestamp ), array( 'class' => 'lock-date' ) ),
			UI_HTML_Tag::create( 'td', $buttonRemove, array( 'class' => 'lock-actions' ) ),
		) );
	}
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		$w->headUser,
		$w->headModule,
		$w->headTitle,
		$w->headDate,
		$w->headActions
	) ) );
	$colgroup	= UI_HTML_Elements::ColumnGroup( '150px', '180px', '', '160px', '120px' );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-not-condensed' ) );
}

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>
';
?>
