<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $backups */

$list	= '<div class="alert alert-info">Keine Backups vorhanden.</div>';
if( $backups ){
	$rows	= [];
	foreach( $backups as $backup ){
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $backup->backupId ),
			HtmlTag::create( 'td', $backup->createdAt ),
		) );
	}
	$thead	= HtmlTag::create( 'thead', '' );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$list	= HtmlTag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table table-striped' ) );
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
