<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Work_Time_Dashboard_My extends Abstraction
{
	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
	}

	public function render()
	{
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$modelTimer		= new Model_Work_Timer( $this->env );
		$hasTimers		= $modelTimer->count( array(
			'workerId'	=> $logicAuth->getCurrentUserId(),
			'status'	=> [1, 2],
		) );
		$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
		$fromAdd	= 'info/dashboard';
		$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;jetzt Zeit erfassen', array(
			'href'	=> './work/time/add?from='.$fromAdd,
			'class'	=> 'btn btn-block btn-success',
		) );
		if( !$hasTimers ){
			$content	= '<div class="alert alert-info">Keine laufende oder pausierte Aktivität vorhanden.</div>'.$buttonAdd;
		}
		else{
			$timer		= $modelTimer->getAllByIndices( array(
				'workerId'	=> $logicAuth->getCurrentUserId(),
				'status'	=> [1, 2],
			), array(
				'status'		=> 'ASC',
				'modifiedAt'	=> 'DESC',
			), [1, 0] )[0];
			View_Helper_Work_Time_Timer::decorateTimer( $this->env, $timer );
			$timePlanned	= View_Helper_Work_Time::formatSeconds( $timer->secondsPlanned );
			$from			= 'info/dashboard';

			if( $timer->status == 1 ){
				$icon		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pause'] );
				$button		= HtmlTag::create( 'a', $icon, array(
					'href'	=> './work/time/pause/'.$timer->workTimerId.'?from='.$from,
					'class'	=> 'btn btn-large btn-warning',
					'title'	=> 'pausieren',
				) );
				$button		= HtmlTag::create( 'a', $icon.'&nbsp;pausieren', array(
					'href'	=> './work/time/pause/'.$timer->workTimerId.'?from='.$from,
					'class'	=> 'btn btn-block btn-warning',
				) );
				$secondsNeeded	= $timer->secondsNeeded + ( time() - $timer->modifiedAt );
				$timeNeeded		= View_Helper_Work_Time::formatSeconds( $secondsNeeded );
				$timeNeeded		= HtmlTag::create( 'span', $timeNeeded, array(
					'id'			=>  "dashboard-timer",
					'data-value'	=>  $secondsNeeded,
				) );
			}
			else{
				$icon		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-play'] );
				$button		= HtmlTag::create( 'a', $icon, array(
					'href'	=> './work/time/start/'.$timer->workTimerId.'?from='.$from,
					'class'	=> 'btn btn-large btn-success',
					'title'	=> 'starten',
				) );
				$button		= HtmlTag::create( 'a', $icon.'&nbsp;starten', array(
					'href'	=> './work/time/start/'.$timer->workTimerId.'?from='.$from,
					'class'	=> 'btn btn-block btn-success',
				) );
				$timeNeeded		= View_Helper_Work_Time::formatSeconds( $timer->secondsNeeded );
				$timeNeeded		= HtmlTag::create( 'span', $timeNeeded, array(
					'data-value'	=>  $timer->secondsNeeded,
				) );
			}

			$linkTimer		= HtmlTag::create( 'a', $timer->title, array(
				'href'	=> './work/time/edit/'.$timer->workTimerId.'?from='.$from,
			) );
			$linkProject	= HtmlTag::create( 'a', $timer->project->title, array(
				'href'	=> './manage/project/view/'.$timer->project->projectId.'?from='.$from,
			) );
			$linkRelation	= HtmlTag::create( 'a', $timer->relationTitle, array(
				'href'	=> join( array(
					$timer->relationLink,
					substr_count( $timer->relationLink, '?' ) ? '&' : '?',
					'from='.$from
				) ),
			) );
			$content	= '
				<div class="row-fluid">
					<div class="span12">
						<div class="autocut">
							<big>'.$linkTimer.'</big>
						</div>
						<div class="autocut">
							<small class="muted">'.$timer->type.':</small><br/>
							'.$linkRelation.'
						</div>
						<div class="autocut">
							<small class="muted">Projekt:</small><br/>
							'.$linkProject.'
						</div>
<!--						<hr/>-->
<!--						<div class="pull-right">'.$button.'</div>-->
						<div class="dashboard-panel-work-timer-container-time">
							<span style="font-size: 1.75em">'.$timeNeeded.'</span><br>
							<small class="not-muted">geplant: <span>'.$timePlanned.'</span></small>
						</div>
						'.$button.'
						'.$buttonAdd.'
					</div>
				</div>
				<script>jQuery(document).ready(function(){WorkTimer.init("#dashboard-timer", "&nbsp;");});</script>';
		}
		return $content;
		$panel	= HtmlTag::create( 'div', array(
			HtmlTag::create( 'h4', 'aktuelle Aktivität' ),
			HtmlTag::create( 'div', $content, array(
				'class' => 'content-panel-inner'
			) )
		), array(
			'class' => 'content-panel content-panel-info'
		) );
		return $panel;
	}
}
