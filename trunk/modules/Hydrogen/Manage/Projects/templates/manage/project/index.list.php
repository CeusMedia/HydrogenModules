<?php
$w			= (object) $words['index'];
$indicator	= new UI_HTML_Indicator();
$helper		= new View_Helper_TimePhraser( $env );
$rows		= array();
foreach( $projects as $project ){
	$cells		= array();
	$url		= './manage/project/edit/'.$project->projectId;
	$link		= UI_HTML_Tag::create( 'a', $project->title, array( 'href' => $url ) );
	$users		= array();
	foreach( $project->users as $projectUser )
		$users[]	= $projectUser->username;
	$desc		= trim( $project->description );
	$graph		= $indicator->build( $project->status + 2, 5, 65 );
	$status		= $words['states'][$project->status];
	$cells[]	= UI_HTML_Tag::create( 'td', '<small>'.$status.'</small><br/>'.$graph );
	$cells[]	= UI_HTML_Tag::create( 'td', $link.'<br/>'.$desc );
	$cells[]	= UI_HTML_Tag::create( 'td', join( ', ', $users ) );
#	$cells[]	= UI_HTML_Tag::create( 'td', $status, array( 'class' => 'project status'.$project->status ) );
	$cells[]	= UI_HTML_Tag::create( 'td', $helper->convert( $project->createdAt, TRUE, 'vor' ) );
	$cells[]	= UI_HTML_Tag::create( 'td', $helper->convert( $project->modifiedAt, TRUE, 'vor' ) );
	$rows[]		= UI_HTML_Tag::create( 'tr', join( $cells ), array( 'class' => count( $rows ) % 2 ? 'even' : 'odd' ) );
}
$heads		= UI_HTML_Elements::TableHeads( array( 'Status', 'Projekt', 'Teilnehmer', 'erstellt', 'geändert' ) );
$colgroup	= UI_HTML_Elements::ColumnGroup( array( '10%', '35%', '25%', '15%', '15%' ) );
$list		= UI_HTML_Tag::create( 'table', $colgroup.$heads.join( $rows ), array( 'class' => 'table table-striped' ) );

$iconAdd	= '<i class="icon-plus icon-white"></i>';
$buttonAdd	= UI_HTML_Elements::LinkButton( './manage/project/add', $iconAdd.' '.$w->buttonAdd, 'btn btn-primary' );
if( !$canAdd )
	$buttonAdd	= UI_HTML_Elements::LinkButton( './manage/project/add', $iconAdd.' '.$w->buttonAdd, 'btn btn-primary disabled', NULL, TRUE );
return '
<div class="content-panel content-panel-list">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
?>