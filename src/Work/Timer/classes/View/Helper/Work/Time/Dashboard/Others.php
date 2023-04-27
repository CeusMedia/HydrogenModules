<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Work_Time_Dashboard_Others extends Abstraction
{
	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function render(): string
	{
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$logicProject	= Logic_Project::getInstance( $this->env );
		$modelTimer		= new Model_Work_Timer( $this->env );
		$coworkers		= $logicProject->getCoworkers( $logicAuth->getCurrentUserId() );
		$hasTimers		= $modelTimer->count( array(
			'workerId'	=> array_keys( $coworkers ),
			'status'	=> [1],
		) );
		if( !$hasTimers ){
			$content	= '<div class="alert alert-info">Keine laufenden Aktivitäten vorhanden.</div>';
		}
		else{
			$timers		= $modelTimer->getAllByIndices( array(
				'workerId'	=> array_keys( $coworkers ),
				'status'	=> [1],
			), [
				'modifiedAt'	=> 'DESC',
			], [10, 0] );
			$rows	= [];
			foreach( $timers as $timer ){
				View_Helper_Work_Time_Timer::decorateTimer( $this->env, $timer );
				$secondsNeeded	= $timer->secondsNeeded + ( time() - $timer->modifiedAt );
				$timePlanned	= View_Helper_Work_Time::formatSeconds( $timer->secondsPlanned );
				$timeNeeded		= View_Helper_Work_Time::formatSeconds( $secondsNeeded );
				$from			= 'info/dashboard';
				$timeNeeded		= HtmlTag::create( 'span', $timeNeeded, [
					'class'			=>  "dashboard-timer-others",
					'data-value'	=>  $secondsNeeded,
				] );
				$linkProject	= HtmlTag::create( 'a', $timer->project->title, [
					'href'	=> './manage/project/view/'.$timer->project->projectId.'?from='.$from,
				] );
				$linkRelation	= HtmlTag::create( 'a', $timer->relationTitle, array(
					'href'	=> join( array(
						$timer->relationLink,
						substr_count( $timer->relationLink, '?' ) ? '&' : '?',
						'from='.$from
					) ),
				) );
				$rows	= HtmlTag::create( 'tr', array(
					HtmlTag::create( 'td', '
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
			$tbody	= HtmlTag::create( 'tbody', $rows );
			$table	= HtmlTag::create( 'table', $tbody, ['class' => 'table table-condensed table-fixed'] );
			$content	= $table.'
			<script>jQuery(document).ready(function(){WorkTimer.init(".dashboard-timer-others", "&nbsp;");});</script>';
		}
		return $content;
	}
}
