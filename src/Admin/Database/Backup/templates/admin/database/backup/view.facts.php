<?php

use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $backup */

$w				= (object) $words['view.facts'];

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconRestore	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cog'] );
$iconDownload	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
	'href'	=> './admin/database/backup/',
	'class'	=> 'btn'
] );
$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, [
	'href'	=> './admin/database/backup/remove/'.$backup->id,
	'class'	=> 'btn btn-danger'
] );

$comment	= $backup->comment['comment'] ?? '';
$comment	= ( '' !== $comment ) ? $comment: '<em class="muted">'.$w->noComment.'</em>';

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				<dl class="dl-horizontal">
					<dt>'.$w->labelFilename.'</dt>
					<dd>'.$backup->filename.'</dd>
					<dt>'.$w->labelComment.'</dt>
					<dd>'.$comment.'</dd>
					<dt>'.$w->labelPath.'</dt>
					<dd>'.substr( $backup->pathname, 0, -1 * strlen( $backup->filename ) ).'</dd>
					<dt>'.$w->labelFilesize.'</dt>
					<dd>'.UnitFormater::formatBytes( $backup->filesize ).'</dd>
					<dt>'.$w->labelCreatedAt.'</dt>
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
