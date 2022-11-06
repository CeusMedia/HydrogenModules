<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */

$w	= (object) $words['index'];
$content	= '<div class="alert alert-info"><em class="muted">'.$w->noCurrent.'</em></div>';


$buttonPause	= '';
$buttonStop		= '';
$buttonEdit		= '';
$buttonAdd		= '<a href="./work/time/add'.$from.'" class="btn btn-success"><i class="fa fa-fw fa-plus"></i> neue Aktivität</a>';
if( $timer ){

	$buttonPause	= '<a href="./work/time/pause/'.$timer->workTimerId.$from.'" class="btn not-btn-large btn-warning"><i class="icon-pause icon-white"></i> pausieren</a>';
	$buttonStop		= '<a href="./work/time/stop/'.$timer->workTimerId.$from.'" class="btn not-btn-large btn-danger"><i class="icon-stop icon-white"></i> abschließen</a>';
	$buttonEdit		= '<a href="./work/time/edit/'.$timer->workTimerId.$from.'" class="btn"><i class="fa fa-fw fa-pencil"></i> bearbeiten</a>';

	$linkProject	= HtmlTag::create( 'a', $timer->project->title, array(
		'href'	=> './manage/project/view/'.$timer->project->projectId,
		'class'	=> 'autocut',
	) );
	$linkRelation	= HtmlTag::create( 'em', 'Nicht zugeordnet.', ['class' => 'muted'] );
	if( $timer->relationTitle ){
		$linkRelation	= $timer->relationTitle;
		if( $timer->relationLink )
			$linkRelation	= HtmlTag::create( 'a', $timer->relationTitle, array(
				'href'	=> $timer->relationLink,
				'class'	=> 'autocut',
			) );
	}
	$labelType	= $timer->type ? $timer->type : 'Zuordnung';
	$seconds	= $timer->secondsNeeded + ( time() - $timer->modifiedAt );
	$from		= $from ? '?from='.$from : '';

	$content	= '
		<div class="row-fluid">
			<div class="span12">
				<dl class="facts-vertical">
					<dt>Projekt</dt>
					<dd><div class="autocut">'.$linkProject.'</div></dd>
					<dt>'.$labelType.'</dt>
					<dd><div class="autocut">'.$linkRelation.'</div></dd>
					<dt>Aktivität</dt>
					<dd><div class="autocut">'.$timer->title.'&nbsp;</div></dd>
					<dt>erfasste Zeit</dt>
					<dd><div id="timer-active" data-value="'.$seconds.'">'.View_Helper_Work_Time::formatSeconds( $seconds ).'</div></dd>
				</dl>
			</div>
		</div>
		<script src="scripts/str_pad.js"></script>
		<script>
$(document).ready(function(){
	WorkTimer.init("#timer-active");
});
		</script>';
}

return '
<div class="content-panel">
	<h3>Aktuelle Aktivität</h3>
	<div class="content-panel-inner">
		'.$content.'
		<div class="buttonbar">
			<div class="btn-group">
				'.$buttonPause.'
				'.$buttonStop.'
			</div>
			'.$buttonEdit.'
			'.$buttonAdd.'
		</div>
	</div>
</div>';
