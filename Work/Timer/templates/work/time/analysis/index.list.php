<?php

$indicator	= new UI_HTML_Indicator( array(
	'useColor'	=> FALSE,
) );

function renderTime( $seconds ){
	if( $seconds )
		return View_Work_Mission::formatSeconds( $seconds );
	return '&minus;';
}


function renderTimers( CMF_Hydrogen_Environment $env, $timers ){
	if( !$timers )
		return '';
	$list	= [];
	foreach( $timers as $timer ){
		View_Helper_Work_Time_Timer::decorateTimer( $env, $timer );

		$linkRelation	= '';
		if( $timer->moduleId ){
			$linkRelation	= UI_HTML_Tag::create( 'a', htmlentities( $timer->relationTitle, ENT_QUOTES, 'UTF-8' ), array(
				'href'		=> $timer->relationLink,
				'class'		=> 'title autocut',
			) );
			$linkRelation	= '<small><span class="muted">'.$timer->type.':</span> '.$linkRelation.'</small>';
			$linkRelation	= UI_HTML_Tag::create( 'div', $linkRelation, array( 'class' => 'autocut' ) );
		}
		$time	= UI_HTML_Tag::create( 'small', '('.renderTime( $timer->secondsNeeded ).')', array( 'class' => 'muted' ) );
		$title	= strlen( trim( $timer->title ) ) ? htmlentities( $timer->title, ENT_QUOTES, 'UTF-8' ) : '<em class="muted">unbenannt</em>';
		$title	= $title.'&nbsp;'.$time;
		$title	= UI_HTML_Tag::create( 'a', $title, array( 'href' => './work/time/edit/'.$timer->workTimerId.'?from=work/time/analysis' ) );
		$title	= UI_HTML_Tag::create( 'div', $title, array( 'class' => 'autocut' ) );

		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $title.$linkRelation ),
		) );
	}
	return UI_HTML_Tag::create( 'table', $list, array( 'class' => 'table table-striped table-condensed' ) );
}

if( !$data )
	return;

//$table	= UI_HTML_Tag::create( 'div', '...', array( 'class' => 'alert alert-info' ) );

if( $filterMode === "users" ){
	$rows	= [];
	foreach( $data as $projectId => $entry ){
		if( $projectId === '@total' )
			continue;
		if( !$entry->secondsPlanned )
			continue;
		$timers	= renderTimers( $env, $entry->timers );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $allProjects[$projectId]->title.$timers ),
			UI_HTML_Tag::create( 'td', renderTime( $entry->secondsPlanned ).'&nbsp;&nbsp;<br/>'.$indicator->build( $entry->secondsPlanned, $data['@total']->secondsPlanned, 100 ), array( 'style' => 'text-align: right' ) ),
			UI_HTML_Tag::create( 'td', renderTime( $entry->secondsNeeded ).'&nbsp;&nbsp;<br/>'.$indicator->build( $entry->secondsNeeded, $data['@total']->secondsNeeded, 100 ), array( 'style' => 'text-align: right' ) ),
		) );
	}
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'big', 'Gesamt' ) ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'big', renderTime( $data['@total']->secondsPlanned ) ).'&nbsp;&nbsp;', array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'big', renderTime( $data['@total']->secondsNeeded ) ).'&nbsp;&nbsp;', array( 'style' => 'text-align: right' ) ),
	) );
	$thead	= UI_HTML_Tag::create( 'thead', array(
		UI_HTML_Tag::create( 'th', 'Projekt' ),
		UI_HTML_Tag::create( 'th', 'geplant', array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'th', 'erfasst', array( 'style' => 'text-align: right' ) ),
	) );
	$colgroup	= UI_HTML_Elements::ColumnGroup( '', '120', '120' );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped table-fixed' ) );
}
else {
	$rows	= [];
	foreach( $data as $userId => $entry ){
		if( $userId === '@total' )
			continue;
		if( !$entry->secondsPlanned )
			continue;
		$timers	= renderTimers( $env, $entry->timers );
		$username	= UI_HTML_Tag::create( 'small', '('.$allUsers[$userId]->firstname.' '.$allUsers[$userId]->surname.')', array( 'class' => 'muted' ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $allUsers[$userId]->username.'&nbsp;'.$username.$timers ),
			UI_HTML_Tag::create( 'td', renderTime( $entry->secondsPlanned ).'&nbsp;&nbsp;<br/>'.$indicator->build( $entry->secondsPlanned, $data['@total']->secondsPlanned, 100 ), array( 'style' => 'text-align: right' ) ),
			UI_HTML_Tag::create( 'td', renderTime( $entry->secondsNeeded ).'&nbsp;&nbsp;<br/>'.$indicator->build( $entry->secondsNeeded, $data['@total']->secondsNeeded, 100 ), array( 'style' => 'text-align: right' ) ),
		) );
	}
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'big', 'Gesamt' ) ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'big', renderTime( $data['@total']->secondsPlanned ) ).'&nbsp;&nbsp;', array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'big', renderTime( $data['@total']->secondsNeeded ) ).'&nbsp;&nbsp;', array( 'style' => 'text-align: right' ) ),
	) );
	$thead	= UI_HTML_Tag::create( 'thead', array(
		UI_HTML_Tag::create( 'th', 'Bearbeiter' ),
		UI_HTML_Tag::create( 'th', 'geplant', array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'th', 'erfasst', array( 'style' => 'text-align: right' ) ),
	) );
	$colgroup	= UI_HTML_Elements::ColumnGroup( '', '120', '120' );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped table-fixed' ) );
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
