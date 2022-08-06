<?php
$w	= (object) $words['index'];

$iconStart		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-play icon-white' ) );
$iconPause		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pause icon-white' ) );
$iconClose		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-stop icon-white' ) );

$iconAdd    = UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd  = UI_HTML_Tag::create( 'a', $iconAdd.' '.$w->buttonAdd, array( 'href' => './work/time/add', 'class' => 'btn btn-small btn-success' ) );

$list		= '<div><em><small class="muted">'.$w->empty.'</small></em></div><br/>';
if( !$timers )
	return $list;

$rows		= [];
$rowClasses	= array(
	0	=> '',
	1	=> 'success',
	2	=> 'warning',
	3	=> 'notice',
);
foreach( $timers as $timer ){
	$urlStart		= './work/time/start/'.$timer->workTimerId;
	$urlPause		= './work/time/pause/'.$timer->workTimerId;
	$urlStop		= './work/time/stop/'.$timer->workTimerId;
/*	$buttonStart 	= UI_HTML_Tag::create( 'button', $iconStart, array(
		'onclick'	=> 'document.location.href=\''.$urlStart.'\';',
		'class'		=> 'btn btn-small btn-success',
		'disabled'	=> $timer->status == 1 ? 'disabled' : NULL,
	) );
	$buttonPause	= UI_HTML_Tag::create( 'button', $iconPause, array(
		'onclick'	=> 'document.location.href=\''.$urlPause.'\';',
		'class'		=> 'btn btn-small btn-warning',
		'disabled'	=> $timer->status != 1 ? 'disabled' : NULL,
	) );
	$buttonStop 	= UI_HTML_Tag::create( 'button', $iconClose, array(
		'onclick'	=> 'document.location.href=\''.$urlStop.'\';',
		'class'		=> 'btn btn-small btn-danger',
		'disabled'	=> $timer->status == 3 ? 'disabled' : NULL,
	) );
	$buttons		= UI_HTML_Tag::create( 'div', $buttonStart.$buttonPause.$buttonStop, array( 'class' => 'btn-group pull-right' ) );
*/
	$secondsNeeded	= $timer->status == 1 ? $timer->secondsNeeded + ( time() - $timer->modifiedAt ) : $timer->secondsNeeded;
	$link			= UI_HTML_Tag::create( 'a', $timer->title, array(
		'href'		=> './work/time/edit/'.$timer->workTimerId.'?from=',
		'class'		=> 'autocut',
	) );

	View_Helper_Work_Time_Timer::decorateTimer( $this->env, $timer );

	$linkRelation	= 'unbekannt';
	if( $timer->moduleId )
		$linkRelation	= UI_HTML_Tag::create( 'a', htmlentities( $timer->relationTitle, ENT_QUOTES, 'UTF-8' ), array(
			'href'	=> $timer->relationLink,
			'class'	=> 'title autocut',
		) );

	$rows[]		= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $link, array( 'class' => 'title' ) ),
		UI_HTML_Tag::create( 'td', $timer->type ),
		UI_HTML_Tag::create( 'td', $linkRelation ),
		UI_HTML_Tag::create( 'td', $timer->project->title ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'span', View_Helper_Work_Time::formatSeconds( $secondsNeeded ), array( 'class' => 'pull-right' ) ) ),
/*		UI_HTML_Tag::create( 'td', $buttons ),*/
	), array( 'class' => $rowClasses[$timer->status] ) );

	$colgroup	= UI_HTML_Elements::ColumnGroup( "", "10%", "20%", "25%", "120" );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', 'Aktivität' ),
		UI_HTML_Tag::create( 'th', 'Typ <small class="muted">(Modul)</small>' ),
		UI_HTML_Tag::create( 'th', 'Aufgabe' ),
		UI_HTML_Tag::create( 'th', 'Projekt' ),
		UI_HTML_Tag::create( 'th', 'Zeit', array( 'class' => 'pull-right' ) ),
	) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
	$pagination	= new \CeusMedia\Bootstrap\Nav\PageControl( './work/time/archive/'.$limit, $page, ceil( $total / $limit ) );
}
return '
<div class="content-panel conten-panel-table">
	<h3>Aktivitäten</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$buttonAdd.'
			'.$pagination->render().'
		</div>
	</div>
</div>';
