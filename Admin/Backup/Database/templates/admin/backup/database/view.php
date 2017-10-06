<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconRestore	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cog' ) );
$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;zurück zur Liste', array(
	'href'	=> './admin/backup/database/',
	'class'	=> 'btn'
) );
$buttonRestore	= UI_HTML_Tag::create( 'a', $iconRestore.' wiederherstellen', array(
	'href'	=> './admin/backup/database/restore/'.$dump->id,
	'class'	=> 'btn btn-primary'
) );
$buttonDownload	= UI_HTML_Tag::create( 'a', $iconDownload.' herunterladen', array(
	'href'	=> './admin/backup/database/download/'.$dump->id,
	'class'	=> 'btn btn-info'
) );
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
	'href'	=> './admin/backup/database/remove/'.$dump->id,
	'class'	=> 'btn btn-danger'
) );

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
					<dd>'.$dump->comment.'</dd>
					<dt>Speicherort</dt>
					<dd>'.substr( $dump->pathname, 0, -1 * strlen( $dump->filename ) ).'</dd>
					<dt>Dateigröße</dt>
					<dd>'.Alg_UnitFormater::formatBytes( $dump->filesize ).'</dd>
					<dt>Erstellungsdatum</dt>
					<dd>'.date( 'Y-m-d H:i:s', $dump->timestamp ).'</dd>
				</dl>
			</div>
		</div>
		<div class="alert alert-danger">
			Das Wiederherstellen einer Sicherung löscht den aktuellen Datenbestand vollständig.<br/>
			<strong>Bitte nur mit Bedacht ausführen!</strong>
		</div>
		<div class="buttonbar">
			'.$buttonCancel.'
			'.$buttonRestore.'
			'.$buttonDownload.'
			'.$buttonRemove.'
		</div>
	</div>
</div>';
