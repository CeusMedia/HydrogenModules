<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['index'];

//$table		= '<div class="info empty"><em><small>'.$w->noEntries.'</small></em></div>';
$table		= HtmlTag::create( 'div', $w->noEntries, ['class' => 'alert alert-info'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );

if( count( $locks ) > 0 ){
	$list	= [];
	foreach( $locks as $lock ){
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, [
			'href'		=> './database/lock/unlock/'.$lock->lockId,
			'class'		=> 'btn btn-mini btn-danger'
		] );
		$moduleTitle	= $lock->module ?: '<em><small class="muted">'.$w->moduleUnknown.'</small></em>';
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $lock->user->username, ['class' => 'lock-user'] ),
			HtmlTag::create( 'td', $moduleTitle, ['class' => 'lock-module'] ),
			HtmlTag::create( 'td', $lock->title, ['class' => 'lock-title'] ),
			HtmlTag::create( 'td', date( 'Y-m-d H:i:s', $lock->timestamp ), ['class' => 'lock-date'] ),
			HtmlTag::create( 'td', $buttonRemove, ['class' => 'lock-actions'] ),
		) );
	}
	$tbody		= HtmlTag::create( 'tbody', $list );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [
		$w->headUser,
		$w->headModule,
		$w->headTitle,
		$w->headDate,
		$w->headActions
	] ) );
	$colgroup	= HtmlElements::ColumnGroup( '150px', '180px', '', '160px', '120px' );
	$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-not-condensed'] );
}

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';
