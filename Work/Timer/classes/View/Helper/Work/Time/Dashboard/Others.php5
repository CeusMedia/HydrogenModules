<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Time_Dashboard_Others extends CMF_Hydrogen_View_Helper_Abstract
{
	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
	}

	public function render()
	{
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$logicProject	= Logic_Project::getInstance( $this->env );
		$modelTimer		= new Model_Work_Timer( $this->env );
		$coworkers		= $logicProject->getCoworkers( $logicAuth->getCurrentUserId() );
		$hasTimers		= $modelTimer->count( array(
			'workerId'	=> array_keys( $coworkers ),
			'status'	=> array( 1 ),
		) );
		if( !$hasTimers ){
			$content	= '<div class="alert alert-info">Keine laufenden Aktivitäten vorhanden.</div>';
		}
		else{
			$timers		= $modelTimer->getAllByIndices( array(
				'workerId'	=> array_keys( $coworkers ),
				'status'	=> array( 1 ),
			), array(
				'modifiedAt'	=> 'DESC',
			), array( 10, 0 ) );
			$rows	= [];
			foreach( $timers as $timer ){
				View_Helper_Work_Time_Timer::decorateTimer( $this->env, $timer );
				$secondsNeeded	= $timer->secondsNeeded + ( time() - $timer->modifiedAt );
				$timePlanned	= View_Helper_Work_Time::formatSeconds( $timer->secondsPlanned );
				$timeNeeded		= View_Helper_Work_Time::formatSeconds( $secondsNeeded );
				$from			= 'info/dashboard';
				$timeNeeded		= UI_HTML_Tag::create( 'span', $timeNeeded, array(
					'class'			=>  "dashboard-timer-others",
					'data-value'	=>  $secondsNeeded,
				) );
				$linkProject	= UI_HTML_Tag::create( 'a', $timer->project->title, array(
					'href'	=> './manage/project/view/'.$timer->project->projectId.'?from='.$from,
				) );
				$linkRelation	= UI_HTML_Tag::create( 'a', $timer->relationTitle, array(
					'href'	=> join( array(
						$timer->relationLink,
						substr_count( $timer->relationLink, '?' ) ? '&' : '?',
						'from='.$from
					) ),
				) );
				$rows	= UI_HTML_Tag::create( 'tr', array(
					UI_HTML_Tag::create( 'td', '
						<div class="autocut">'.$timer->title.'</div>
						<div class="autocut">
							<small class="muted">'.$timer->workerId.' @ '.$timer->type.':</small><br/>
							<small>'.$linkRelation.'</small>
						</div>
						<div class="autocut">
							<small class="muted">Zeit:</small>
							<span>'.$timeNeeded.'</span> <small class="not-muted">(geplant: <span>'.$timePlanned.'</span>)</small>
						</div>
					',
					array( 'class' => NULL ) ),
				) );
			}
			$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
			$table	= UI_HTML_Tag::create( 'table', $tbody, array( 'class' => 'table table-condensed table-fixed' ) );
			$content	= $table.'
			<script>jQuery(document).ready(function(){WorkTimer.init(".dashboard-timer-others", "&nbsp;");});</script>';
		}
		return $content;
	}
}
