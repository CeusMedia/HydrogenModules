<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w				= (object) $words['index'];
$indicator		= new UI_HTML_Indicator();
$helperTime		= new View_Helper_TimePhraser( $env );

//$iconDefault	= HtmlTag::create( 'i', '', array( 'class' => 'icon-star' ) );
$iconDefault	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-star' ) );
//if( $env->getModules()->has( 'UI_Font_FontAwesome' ) )
//	$iconDefault	= HtmlTag::create( 'b', '', array( 'class' => 'fa fa-star' ) );

$pagination		= new \CeusMedia\Bootstrap\PageControl( './manage/project', $page, ceil( $total / $filterLimit ), array( 'shortenFirst' => FALSE ) );
$pagination		= $pagination->render();

$list	= '<div><em class="muted">'.$w->noEntries.'</em></div><br/>';
if( $projects ){
	$rows		= [];
	foreach( $projects as $project ){
		$cells		= [];
		$url		= './manage/project/view/'.$project->projectId;
		$label		= $project->title.( $project->isDefault ? '&nbsp;'.$iconDefault : '' );
		$link		= HtmlTag::create( 'a', $label, array( 'href' => $url ) );
		$users		= [];
		foreach( $project->users as $projectUser ){
			if( $projectUser->userId === $project->creatorId )
				$users[]	= HtmlTag::create( 'u', $projectUser->username );
			else
				$users[]	= $projectUser->username;
		}

//		$desc	= explode( "\n", trim( strip_tags( $project->description ) ) );
//		if( $desc )
//			$desc	= HtmlTag::create( 'small', $desc[0], array( 'class' => 'not-muted' ) );
		$graph		= $indicator->build( $project->status + 2, 5, "100%" );
		$status		= htmlentities( $words['states'][$project->status], ENT_QUOTES, 'utf-8' );
		$priority	= htmlentities( $words['priorities'][$project->priority], ENT_QUOTES, 'utf-8' );
		$priority	= HtmlTag::create( 'abbr', $project->priority, array( 'title' => $priority ) );

		$dateChange	= max( $project->createdAt, $project->modifiedAt );

		$cells[]	= HtmlTag::create( 'td', $priority, array( 'title' => $priority, 'class' => 'cell-priority priority-'.$project->priority ) );
		$cells[]	= HtmlTag::create( 'td', $link, array( 'class' => 'cell-title' ) );
		$cells[]	= HtmlTag::create( 'td', join( ', ', $users ), array( 'class' => 'cell-users' ) );
	#	$cells[]	= HtmlTag::create( 'td', $status, array( 'class' => 'project status'.$project->status ) );
		$cells[]	= HtmlTag::create( 'td', $graph.'<br/>', array( 'class' => 'cell-status', 'title' => $status ) );
		$cells[]	= HtmlTag::create( 'td', $helperTime->convert( $dateChange, TRUE, 'vor' ), array( 'class' => 'cell-change' ) );
		$rows[]		= HtmlTag::create( 'tr', join( $cells ), array( 'class' => count( $rows ) % 2 ? 'even' : 'odd' ) );
	}
	$heads		= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', $w->headPriority, array( 'class' => 'row-priority' ) ),
		HtmlTag::create( 'th', $w->headTitle, array( 'class' => 'row-title' ) ),
		HtmlTag::create( 'th', $w->headUsers, array( 'class' => 'row-users' ) ),
		HtmlTag::create( 'th', $w->headStatus, array( 'class' => 'row-status' ) ),
		HtmlTag::create( 'th', $w->headChanged, array( 'class' => 'row-changed' ) ),
	) );
	$thead		= HtmlTag::create( 'thead', $heads );
	$tbody		= HtmlTag::create( 'tbody', join( $rows ) );
	$colgroup	= UI_HTML_Elements::ColumnGroup( array( '30px', '35%', '25%', '10%', '15%' ) );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

//$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => "icon-plus icon-white" ) );
$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$buttonAdd		= UI_HTML_Elements::LinkButton( './manage/project/add', $iconAdd.'&nbsp;'.$w->buttonAdd, 'btn btn-success not-btn-small' );
$buttonAddSmall	= HtmlTag::create( 'a', $iconAdd, array(
	'href'	=> './manage/project/add',
	'class'	=> 'btn btn-success btn-mini',
) );

if( !$canAdd ){
	$buttonAdd	= UI_HTML_Elements::LinkButton( './manage/project/add', $iconAdd.' '.$w->buttonAdd, 'btn btn-success btn-small disabled', NULL, TRUE );
	$nuttonAddSmall	= "";
}

return '
<div class="content-panel content-panel-list">
	<h3>'.$w->heading.'<!--&nbsp;'.$buttonAddSmall.'--></h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$pagination.'
			'.$buttonAdd.'
		</div>
	</div>
</div>
<style>
td.cell-priority,
td.cell-change {
	font-size: 0.9em;
	}
</style>
';
?>
