<?php

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var array<int, object> $timers */
/** @var int $limit */
/** @var int $page */
/** @var int $total */

$w	= (object) $words['index'];

$iconStart		= HtmlTag::create( 'i', '', ['class' => 'icon-play icon-white'] );
$iconPause		= HtmlTag::create( 'i', '', ['class' => 'icon-pause icon-white'] );
$iconClose		= HtmlTag::create( 'i', '', ['class' => 'icon-stop icon-white'] );

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' '.$w->buttonAdd, ['href' => './work/time/add', 'class' => 'btn btn-small btn-success'] );

$list		= '<div><em><small class="muted">'.$w->empty.'</small></em></div><br/>';
if( !$timers )
	return $list;

$rows		= [];
$rowClasses	= [
	0	=> '',
	1	=> 'success',
	2	=> 'warning',
	3	=> 'notice',
];
foreach( $timers as $timer ){
	$urlStart		= './work/time/start/'.$timer->workTimerId;
	$urlPause		= './work/time/pause/'.$timer->workTimerId;
	$urlStop		= './work/time/stop/'.$timer->workTimerId;
/*	$buttonStart 	= HtmlTag::create( 'button', $iconStart, array(
		'onclick'	=> 'document.location.href=\''.$urlStart.'\';',
		'class'		=> 'btn btn-small btn-success',
		'disabled'	=> $timer->status == 1 ? 'disabled' : NULL,
	) );
	$buttonPause	= HtmlTag::create( 'button', $iconPause, array(
		'onclick'	=> 'document.location.href=\''.$urlPause.'\';',
		'class'		=> 'btn btn-small btn-warning',
		'disabled'	=> $timer->status != 1 ? 'disabled' : NULL,
	) );
	$buttonStop 	= HtmlTag::create( 'button', $iconClose, array(
		'onclick'	=> 'document.location.href=\''.$urlStop.'\';',
		'class'		=> 'btn btn-small btn-danger',
		'disabled'	=> $timer->status == 3 ? 'disabled' : NULL,
	) );
	$buttons		= HtmlTag::create( 'div', $buttonStart.$buttonPause.$buttonStop, ['class' => 'btn-group pull-right'] );
*/
	$secondsNeeded	= $timer->status == 1 ? $timer->secondsNeeded + ( time() - $timer->modifiedAt ) : $timer->secondsNeeded;
	$link			= HtmlTag::create( 'a', $timer->title, array(
		'href'		=> './work/time/edit/'.$timer->workTimerId.'?from=',
		'class'		=> 'autocut',
	) );

	View_Helper_Work_Time_Timer::decorateTimer( $this->env, $timer );

	$linkRelation	= 'unbekannt';
	if( $timer->moduleId )
		$linkRelation	= HtmlTag::create( 'a', htmlentities( $timer->relationTitle, ENT_QUOTES, 'UTF-8' ), array(
			'href'	=> $timer->relationLink,
			'class'	=> 'title autocut',
		) );

	$rows[]		= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $link, ['class' => 'title'] ),
		HtmlTag::create( 'td', $timer->type ),
		HtmlTag::create( 'td', $linkRelation ),
		HtmlTag::create( 'td', $timer->project->title ),
		HtmlTag::create( 'td', HtmlTag::create( 'span', View_Helper_Work_Time::formatSeconds( $secondsNeeded ), ['class' => 'pull-right'] ) ),
/*		HtmlTag::create( 'td', $buttons ),*/
	), ['class' => $rowClasses[$timer->status]] );
}

$colgroup	= HtmlElements::ColumnGroup( "", "10%", "20%", "25%", "120" );
$thead		= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
	HtmlTag::create( 'th', 'Aktivität' ),
	HtmlTag::create( 'th', 'Typ <small class="muted">(Modul)</small>' ),
	HtmlTag::create( 'th', 'Aufgabe' ),
	HtmlTag::create( 'th', 'Projekt' ),
	HtmlTag::create( 'th', 'Zeit', ['class' => 'pull-right'] ),
) ) );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );
$pagination	= new PageControl( './work/time/archive/'.$limit, $page, ceil( $total / $limit ) );

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
