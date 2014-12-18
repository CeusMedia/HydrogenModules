<?php
$w			= (object) $words['index'];
$indicator	= new UI_HTML_Indicator();
$helper		= new View_Helper_TimePhraser( $env );

$pagination	= "";
if( $filterLimit && $total > $filterLimit ){
	$uri		= './manage/project';
	$pagination	= new View_Helper_Pagination( $options );
	$pagination	= $pagination->render( $uri, $total, $filterLimit, $page );
}

if( $projects ){
	$rows		= array();
	foreach( $projects as $project ){
		$cells		= array();
		$url		= './manage/project/edit/'.$project->projectId;
		$link		= UI_HTML_Tag::create( 'a', $project->title, array( 'href' => $url ) );
		$users		= array();
		foreach( $project->users as $projectUser )
			$users[]	= $projectUser->username;
	//	$desc		= trim( $project->description );
		$graph		= $indicator->build( $project->status + 2, 5, 65 );
		$status		= htmlentities( $words['states'][$project->status], ENT_QUOTES, 'utf-8' );
		$priority	= htmlentities( $words['priorities'][$project->priority], ENT_QUOTES, 'utf-8' );

		$dateChange	= max( $project->createdAt, $project->modifiedAt );

		$cells[]	= UI_HTML_Tag::create( 'td', $graph, array( 'class' => 'cell-status', 'title' => $status ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $link, array( 'class' => 'cell-title' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', join( ', ', $users ), array( 'class' => 'cell-users' ) );
	#	$cells[]	= UI_HTML_Tag::create( 'td', $status, array( 'class' => 'project status'.$project->status ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $project->priority, array( 'title' => $priority, 'class' => 'cell-priority priority-'.$project->priority ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $helper->convert( $dateChange, TRUE, 'vor' ), array( 'class' => 'cell-change' ) );
		$rows[]		= UI_HTML_Tag::create( 'tr', join( $cells ), array( 'class' => count( $rows ) % 2 ? 'even' : 'odd' ) );
	}
	//$heads		= UI_HTML_Elements::TableHeads( array( 'Status', 'Projekt', 'Teilnehmer', 'Prio', 'verändert' ) );
	$heads		= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Status', array( 'class' => 'row-status' ) ),
		UI_HTML_Tag::create( 'th', 'Projekt', array( 'class' => 'row-title' ) ),
		UI_HTML_Tag::create( 'th', 'Teilnehmer', array( 'class' => 'row-users' ) ),
		UI_HTML_Tag::create( 'th', 'Prio', array( 'class' => 'row-priority' ) ),
		UI_HTML_Tag::create( 'th', 'verändert', array( 'class' => 'row-changed' ) ),
	) );
	$thead		= UI_HTML_Tag::create( 'thead', $heads );
	$tbody		= UI_HTML_Tag::create( 'tbody', join( $rows ) );
	$colgroup	= UI_HTML_Elements::ColumnGroup( array( '10%', '35%', '25%', '30px', '15%' ) );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}
else
	$list	= '<div><em class="muted">Keine Projekte gefunden.</em></div><br/>';

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
			<div class="pull-right">
				'.$pagination.'
			</div>
			<div class="pull-left">
				'.$buttonAdd.'
			</div>
			<div style="clear: both"></div>
		</div>
	</div>
</div>';
?>
