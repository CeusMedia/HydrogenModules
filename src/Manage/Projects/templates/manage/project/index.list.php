<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Indicator as HtmlIndicator;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w				= (object) $words['index'];
$indicator		= new HtmlIndicator();
$helperTime		= new View_Helper_TimePhraser( $env );

//$iconDefault	= HtmlTag::create( 'i', '', ['class' => 'icon-star'] );
$iconDefault	= HtmlTag::create( 'i', '', ['class' => 'fa fa-star'] );
//if( $env->getModules()->has( 'UI_Font_FontAwesome' ) )
//	$iconDefault	= HtmlTag::create( 'b', '', ['class' => 'fa fa-star'] );

$pagination		= new \CeusMedia\Bootstrap\PageControl( './manage/project', $page, ceil( $total / $filterLimit ), ['shortenFirst' => FALSE] );
$pagination		= $pagination->render();

$list	= '<div><em class="muted">'.$w->noEntries.'</em></div><br/>';
if( $projects ){
	$rows		= [];
	foreach( $projects as $project ){
		$cells		= [];
		$url		= './manage/project/view/'.$project->projectId;
		$label		= $project->title.( $project->isDefault ? '&nbsp;'.$iconDefault : '' );
		$link		= HtmlTag::create( 'a', $label, ['href' => $url] );
		$users		= [];
		foreach( $project->users as $projectUser ){
			if( $projectUser->userId === $project->creatorId )
				$users[]	= HtmlTag::create( 'u', $projectUser->username );
			else
				$users[]	= $projectUser->username;
		}

//		$desc	= explode( "\n", trim( strip_tags( $project->description ) ) );
//		if( $desc )
//			$desc	= HtmlTag::create( 'small', $desc[0], ['class' => 'not-muted'] );
		$graph		= $indicator->build( $project->status + 2, 5, "100%" );
		$status		= htmlentities( $words['states'][$project->status], ENT_QUOTES, 'utf-8' );
		$priority	= htmlentities( $words['priorities'][$project->priority], ENT_QUOTES, 'utf-8' );
		$priority	= HtmlTag::create( 'abbr', $project->priority, ['title' => $priority] );

		$dateChange	= max( $project->createdAt, $project->modifiedAt );

		$cells[]	= HtmlTag::create( 'td', $priority, ['title' => $priority, 'class' => 'cell-priority priority-'.$project->priority] );
		$cells[]	= HtmlTag::create( 'td', $link, ['class' => 'cell-title'] );
		$cells[]	= HtmlTag::create( 'td', join( ', ', $users ), ['class' => 'cell-users'] );
	#	$cells[]	= HtmlTag::create( 'td', $status, ['class' => 'project status'.$project->status] );
		$cells[]	= HtmlTag::create( 'td', $graph.'<br/>', ['class' => 'cell-status', 'title' => $status] );
		$cells[]	= HtmlTag::create( 'td', $helperTime->convert( $dateChange, TRUE, 'vor' ), ['class' => 'cell-change'] );
		$rows[]		= HtmlTag::create( 'tr', join( $cells ), ['class' => count( $rows ) % 2 ? 'even' : 'odd'] );
	}
	$heads		= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', $w->headPriority, ['class' => 'row-priority'] ),
		HtmlTag::create( 'th', $w->headTitle, ['class' => 'row-title'] ),
		HtmlTag::create( 'th', $w->headUsers, ['class' => 'row-users'] ),
		HtmlTag::create( 'th', $w->headStatus, ['class' => 'row-status'] ),
		HtmlTag::create( 'th', $w->headChanged, ['class' => 'row-changed'] ),
	) );
	$thead		= HtmlTag::create( 'thead', $heads );
	$tbody		= HtmlTag::create( 'tbody', join( $rows ) );
	$colgroup	= HtmlElements::ColumnGroup( ['30px', '35%', '25%', '10%', '15%'] );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );
}

//$iconAdd		= HtmlTag::create( 'i', '', ['class' => "icon-plus icon-white"] );
$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$buttonAdd		= HtmlElements::LinkButton( './manage/project/add', $iconAdd.'&nbsp;'.$w->buttonAdd, 'btn btn-success not-btn-small' );
$buttonAddSmall	= HtmlTag::create( 'a', $iconAdd, [
	'href'	=> './manage/project/add',
	'class'	=> 'btn btn-success btn-mini',
] );

if( !$canAdd ){
	$buttonAdd	= HtmlElements::LinkButton( './manage/project/add', $iconAdd.' '.$w->buttonAdd, 'btn btn-success btn-small disabled', NULL, TRUE );
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
