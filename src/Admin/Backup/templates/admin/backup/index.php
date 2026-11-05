<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $backups */

$w		= (object) $words['index'];
$wf		= (object) $words['index.filter'];

$list	= '<div class="alert alert-info">'.$w->noEntries.'</div>';
if( $backups ){
	$rows	= [];
	foreach( $backups as $backup ){
		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $backup->backupId ),
			HtmlTag::create( 'td', $backup->createdAt ),
		] );
	}
	$thead	= HtmlTag::create( 'thead', '' );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$list	= HtmlTag::create( 'table', [$thead, $tbody], ['class' => 'table table-striped'] );
}

$panelList	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			<a href="./admin/backup/add" class="btn btn-primary">'.$w->buttonAdd.'</a>
		</div>
	</div>
</div>';

$panelFilter	= '
<div class="content-panel">
	<h3>'.$wf->heading.'</h3>
	<div class="content-panel-inner">
		<p>'.$wf->notImplemented.'</p>
	</div>
</div>';

return $panelList;
