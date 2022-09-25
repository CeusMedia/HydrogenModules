<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconRestore	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cog' ) );
$iconDownload	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;zurück zur Liste', array(
	'href'	=> './admin/database/backup/',
	'class'	=> 'btn'
) );
$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
	'href'	=> './admin/database/backup/remove/'.$backup->id,
	'class'	=> 'btn btn-danger'
) );

$comment	= $backup->comment['comment'] ? $backup->comment['comment'] : '<em class="muted">Kein Kommentar</em>';

return '
<div class="content-panel">
	<h3>Sicherung</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				<dl class="dl-horizontal">
					<dt>Dateiname</dt>
					<dd>'.$backup->filename.'</dd>
					<dt>Kommentar</dt>
					<dd>'.$comment.'</dd>
					<dt>Speicherort</dt>
					<dd>'.substr( $backup->pathname, 0, -1 * strlen( $backup->filename ) ).'</dd>
					<dt>Dateigröße</dt>
					<dd>'.Alg_UnitFormater::formatBytes( $backup->filesize ).'</dd>
					<dt>Erstellungsdatum</dt>
					<dd>'.date( 'Y-m-d', $backup->timestamp ).' <small class="muted">'.date( 'H:i:s', $backup->timestamp ).'</small></dd>
				</dl>
			</div>
		</div>
		<div class="buttonbar">
			'.$buttonCancel.'
			'.$buttonRemove.'
		</div>
	</div>
</div>';
