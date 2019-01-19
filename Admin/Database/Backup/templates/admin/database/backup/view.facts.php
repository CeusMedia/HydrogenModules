<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconRestore	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cog' ) );
$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;zurück zur Liste', array(
	'href'	=> './admin/database/backup/',
	'class'	=> 'btn'
) );
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
	'href'	=> './admin/database/backup/remove/'.$dump->id,
	'class'	=> 'btn btn-danger'
) );

$comment	= $dump->comment['comment'] ? $dump->comment['comment'] : '<em class="muted">Kein Kommentar</em>';

return '
<div class="content-panel">
	<h3>Sicherung</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				<dl class="dl-horizontal">
					<dt>Dateiname</dt>
					<dd>'.$dump->filename.'</dd>
					<dt>Kommentar</dt>
					<dd>'.$comment.'</dd>
					<dt>Speicherort</dt>
					<dd>'.substr( $dump->pathname, 0, -1 * strlen( $dump->filename ) ).'</dd>
					<dt>Dateigröße</dt>
					<dd>'.Alg_UnitFormater::formatBytes( $dump->filesize ).'</dd>
					<dt>Erstellungsdatum</dt>
					<dd>'.date( 'Y-m-d', $dump->timestamp ).' <small class="muted">'.date( 'H:i:s', $dump->timestamp ).'</small></dd>
				</dl>
			</div>
		</div>
		<div class="buttonbar">
			'.$buttonCancel.'
			'.$buttonRemove.'
		</div>
	</div>
</div>';
