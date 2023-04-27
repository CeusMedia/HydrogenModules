<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Indicator as HtmlIndicator;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var array $allProjects */
/** @var array $allUsers */
/** @var array $data */

$indicator	= new HtmlIndicator( [
	'useColor'	=> FALSE,
] );

function renderTime( int $seconds ): string
{
	if( $seconds )
		return View_Work_Mission::formatSeconds( $seconds );
	return '&minus;';
}


function renderTimers( Web $env, $timers ): string
{
	if( !$timers )
		return '';
	$list	= [];
	foreach( $timers as $timer ){
		View_Helper_Work_Time_Timer::decorateTimer( $env, $timer );

		$linkRelation	= '';
		if( $timer->moduleId ){
			$linkRelation	= HtmlTag::create( 'a', htmlentities( $timer->relationTitle, ENT_QUOTES, 'UTF-8' ), [
				'href'		=> $timer->relationLink,
				'class'		=> 'title autocut',
			] );
			$linkRelation	= '<small><span class="muted">'.$timer->type.':</span> '.$linkRelation.'</small>';
			$linkRelation	= HtmlTag::create( 'div', $linkRelation, ['class' => 'autocut'] );
		}
		$time	= HtmlTag::create( 'small', '('.renderTime( $timer->secondsNeeded ).')', ['class' => 'muted'] );
		$title	= strlen( trim( $timer->title ) ) ? htmlentities( $timer->title, ENT_QUOTES, 'UTF-8' ) : '<em class="muted">unbenannt</em>';
		$title	= $title.'&nbsp;'.$time;
		$title	= HtmlTag::create( 'a', $title, ['href' => './work/time/edit/'.$timer->workTimerId.'?from=work/time/analysis'] );
		$title	= HtmlTag::create( 'div', $title, ['class' => 'autocut'] );

		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $title.$linkRelation ),
		) );
	}
	return HtmlTag::create( 'table', $list, ['class' => 'table table-striped table-condensed'] );
}

if( !$data )
	return;

//$table	= HtmlTag::create( 'div', '...', ['class' => 'alert alert-info'] );

if( $filterMode === "users" ){
	$rows	= [];
	foreach( $data as $projectId => $entry ){
		if( $projectId === '@total' )
			continue;
		if( !$entry->secondsPlanned )
			continue;
		$timers	= renderTimers( $env, $entry->timers );
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $allProjects[$projectId]->title.$timers ),
			HtmlTag::create( 'td', renderTime( $entry->secondsPlanned ).'&nbsp;&nbsp;<br/>'.$indicator->build( $entry->secondsPlanned, $data['@total']->secondsPlanned, 100 ), ['style' => 'text-align: right'] ),
			HtmlTag::create( 'td', renderTime( $entry->secondsNeeded ).'&nbsp;&nbsp;<br/>'.$indicator->build( $entry->secondsNeeded, $data['@total']->secondsNeeded, 100 ), ['style' => 'text-align: right'] ),
		) );
	}
	$rows[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', HtmlTag::create( 'big', 'Gesamt' ) ),
		HtmlTag::create( 'td', HtmlTag::create( 'big', renderTime( $data['@total']->secondsPlanned ) ).'&nbsp;&nbsp;', ['style' => 'text-align: right'] ),
		HtmlTag::create( 'td', HtmlTag::create( 'big', renderTime( $data['@total']->secondsNeeded ) ).'&nbsp;&nbsp;', ['style' => 'text-align: right'] ),
	) );
	$thead	= HtmlTag::create( 'thead', array(
		HtmlTag::create( 'th', 'Projekt' ),
		HtmlTag::create( 'th', 'geplant', ['style' => 'text-align: right'] ),
		HtmlTag::create( 'th', 'erfasst', ['style' => 'text-align: right'] ),
	) );
	$colgroup	= HtmlElements::ColumnGroup( '', '120', '120' );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped table-fixed'] );
}
else {
	$rows	= [];
	foreach( $data as $userId => $entry ){
		if( $userId === '@total' )
			continue;
		if( !$entry->secondsPlanned )
			continue;
		$timers	= renderTimers( $env, $entry->timers );
		$username	= HtmlTag::create( 'small', '('.$allUsers[$userId]->firstname.' '.$allUsers[$userId]->surname.')', ['class' => 'muted'] );
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $allUsers[$userId]->username.'&nbsp;'.$username.$timers ),
			HtmlTag::create( 'td', renderTime( $entry->secondsPlanned ).'&nbsp;&nbsp;<br/>'.$indicator->build( $entry->secondsPlanned, $data['@total']->secondsPlanned, 100 ), ['style' => 'text-align: right'] ),
			HtmlTag::create( 'td', renderTime( $entry->secondsNeeded ).'&nbsp;&nbsp;<br/>'.$indicator->build( $entry->secondsNeeded, $data['@total']->secondsNeeded, 100 ), ['style' => 'text-align: right'] ),
		) );
	}
	$rows[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', HtmlTag::create( 'big', 'Gesamt' ) ),
		HtmlTag::create( 'td', HtmlTag::create( 'big', renderTime( $data['@total']->secondsPlanned ) ).'&nbsp;&nbsp;', ['style' => 'text-align: right'] ),
		HtmlTag::create( 'td', HtmlTag::create( 'big', renderTime( $data['@total']->secondsNeeded ) ).'&nbsp;&nbsp;', ['style' => 'text-align: right'] ),
	) );
	$thead	= HtmlTag::create( 'thead', array(
		HtmlTag::create( 'th', 'Bearbeiter' ),
		HtmlTag::create( 'th', 'geplant', ['style' => 'text-align: right'] ),
		HtmlTag::create( 'th', 'erfasst', ['style' => 'text-align: right'] ),
	) );
	$colgroup	= HtmlElements::ColumnGroup( '', '120', '120' );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped table-fixed'] );
}

//print_m( $data );
//die;

return '
<div class="content-panel">
	<h3>Analysis</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			<button type="button" class="btn btn-small not-btn-info"><i class="fa fa-fw fa-download"></i>&nbsp;exportieren als CSV</button>
		</div>
	</div>
</div>
<style>
div.indicator span.indicator-outer{
	border: 1px solid rgba(127, 127, 127, 0.6);
	border-radius: 2px;
	}
div.indicator div.indicator-inner {
	float: right;
	height: 6px;
	background-color: rgba(127, 127, 127, 0.3);
	}
</style>';
