<?php

$iconRestore	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cog' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$buttonCreateCopy	= UI_HTML_Tag::create( 'a', $iconRestore.' Kopie installieren', array(
	'href'	=> './admin/database/backup/copy/create/'.$dump->id,
	'class'	=> 'btn btn-primary'
) );
$buttonRemoveCopy	= UI_HTML_Tag::create( 'a', $iconRemove.' Kopie entfernen', array(
	'href'	=> './admin/database/backup/copy/remove/'.$dump->id,
	'class'	=> 'btn btn-danger'
) );
$facts					= 'Eine Sicherung kann als Kopie in der Datenbank installiert werden.<br/>Diese Kopie kann zur temporären Ansicht für den aktuellen Benutzer aktiviert werden.<br/>Der Kopiervorgang kann, abhängig von der Datenbankgröße, einige Zeit beanspruchen.';
$buttonActivateCopy		= '';
$buttonDeactivateCopy	= '';
if( !empty( $dump->comment['copyPrefix'] ) ){
	$buttonCreateCopy		= '';
	$buttonDeactivateCopy	= '';
	$buttonActivateCopy		= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp; Kopie aktivieren', array(
		'href'	=> './admin/database/backup/copy/activate/'.$dump->id,
		'class'	=> 'btn btn-success'
	) );
	$facts	= '
		<dl class="dl-horizontal">
			<dt>Präfix</dt>
			<dd>'.$dump->comment['copyPrefix'].'</dd>
			<dt>Erstellungsdatum</dt>
			<dd>'.date( 'Y-m-d H:i:s', (float) $dump->comment['copyTimestamp'] ).'</dd>
		</dl>';
	if( $dump->comment['copyPrefix'] === $currentCopyPrefix ){
		$buttonActivateCopy		= '';
		$buttonRemoveCopy		= '';
		$buttonDeactivateCopy	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp; Kopie deaktivieren', array(
			'href'	=> './admin/database/backup/copy/deactivate/'.$dump->id,
			'class'	=> 'btn btn-inverse'
		) );
	}
}

return '
<div class="content-panel">
	<h3>Kopie erstellen und aktivieren</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				'.$facts.'
			</div>
		</div>
		<div class="buttonbar">
			'.$buttonCreateCopy.'
			'.$buttonActivateCopy.'
			'.$buttonDeactivateCopy.'
		</div>
	</div>
</div>';
