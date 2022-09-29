<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $backup */

$iconRestore	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cog' ) );
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$buttonCreateCopy	= HtmlTag::create( 'a', $iconRestore.' Kopie installieren', array(
	'href'	=> './admin/database/backup/copy/create/'.$backup->id,
	'class'	=> 'btn btn-primary'
) );
$facts					= 'Eine Sicherung kann als Kopie in der Datenbank installiert werden.<br/>Diese Kopie kann zur temporären Ansicht für den aktuellen Benutzer aktiviert werden.<br/>Der Kopiervorgang kann, abhängig von der Datenbankgröße, einige Zeit beanspruchen.';
$buttonActivateCopy		= '';
$buttonDeactivateCopy	= '';
$buttonRemoveCopy		= '';
if( !empty( $backup->comment['copyPrefix'] ) ){
	$buttonCreateCopy		= '';
	$buttonDeactivateCopy	= '';
	$buttonActivateCopy		= HtmlTag::create( 'a', $iconRemove.'&nbsp; Kopie aktivieren', array(
		'href'	=> './admin/database/backup/copy/activate/'.$backup->id,
		'class'	=> 'btn btn-success'
	) );
	$buttonRemoveCopy	= HtmlTag::create( 'a', $iconRemove.' Kopie entfernen', array(
		'href'	=> './admin/database/backup/copy/drop/'.$backup->id,
		'class'	=> 'btn btn-danger'
	) );
	$facts	= '
		<dl class="dl-horizontal">
			<dt>Präfix</dt>
			<dd>'.$backup->comment['copyPrefix'].'</dd>
			<dt>Erstellungsdatum</dt>
			<dd>'.date( 'Y-m-d H:i:s', (float) $backup->comment['copyTimestamp'] ).'</dd>
		</dl>';
	if( $backup->comment['copyPrefix'] === $currentCopyPrefix ){
		$buttonActivateCopy		= '';
		$buttonRemoveCopy		= '';
		$buttonDeactivateCopy	= HtmlTag::create( 'a', $iconRemove.'&nbsp; Kopie deaktivieren', array(
			'href'	=> './admin/database/backup/copy/deactivate/'.$backup->id,
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
			'.$buttonRemoveCopy.'
		</div>
	</div>
</div>';
