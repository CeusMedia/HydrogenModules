<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Work_Mission_Dashboard_MyEvents extends Abstraction
{
	protected array $events		= [];

	protected array $projects		= [];

	/**
	 * @param Environment $env
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
	}

	public function render(): string
	{
		$words			= $this->env->getLanguage()->getWords( 'work/mission' );
		$today			= HtmlTag::create( 'div', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'small', HtmlTag::create( 'abbr', 'KW', ['title' => "Kalenderwoche"] ) ),
				HtmlTag::create( 'br' ),
				HtmlTag::create( 'span', (int) date( 'W' ), [
					'style' => 'font-size: 2em;'
				] ),
			), ['style' => 'text-align: center; float: right; width: 50px'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'span', $words['days'][date( 'w' )], ['style' => 'font-size: 1.8em'] ),
				HtmlTag::create( 'br' ),
				HtmlTag::create( 'span', date( 'd' ).'. '.$words['months'][date( 'n' )].' '.date( 'Y' ), [
					'style' => 'font-size: 1.1em'
				] ),
			), ['style' => 'text-align: center'] ),
		) );
		$content	= HtmlTag::create( 'div', 'Keine Termine.', ['class' => 'alert alert-info'] );
		if( $this->events ){
			$rows	= [];
			foreach( $this->events as $event ){
				$project		= $this->projects[$event->projectId];
				$labelProject	= HtmlTag::create( 'span', $project->title, [
					'style'		=> 'font-size: smaller'
				] );
				$link			= HtmlTag::create( 'a', $event->title, [
					'href'		=> './work/mission/view/'.$event->missionId,
					'style'		=> 'font-size: larger'
				] );
				$label	= $link.'<br/>'.$labelProject;
				$rows[]	= HtmlTag::create( 'tr', array(
					HtmlTag::create( 'td', $this->renderNiceTime( $event->timeStart ).'<br/><small class="muted">'.$this->renderNiceTime( $event->timeEnd ).'</small>' ),
					HtmlTag::create( 'td', $label, ['class' => 'autocut'] ),
//							HtmlTag::create( 'td', '#'.$event->priority ),
				) );
			}
			$colgroup	= HtmlElements::ColumnGroup( [
				'50px',
//						'20px',
				'',
			] );
			$tbody		= HtmlTag::create( 'tbody', $rows );
			$content	= HtmlTag::create( 'table', $colgroup.$tbody, [
				'class'	=> 'table table-condensed table-fixed'
			] );
		}
		$buttonAdd	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-plus"></i>&nbsp;neuer Termin', [
			'href'	=> './work/mission/add?type=1',
			'class'	=> 'btn btn-block btn-success',
		] );
		return '<br/>'.$today.'<br/>'.$content.$buttonAdd;
	}

	protected function renderNiceTime( $time ): string
	{
		if( !strlen( trim( $time ) ) )
			return '-';
		[$hours, $minutes] = explode( ':', $time );
		return HtmlTag::create( 'span', [
			HtmlTag::create( 'big', str_pad( $hours, 2, 0, STR_PAD_LEFT ) ),
			HtmlTag::create( 'sup', str_pad( $minutes, 2, 0, STR_PAD_LEFT ) ),
		], ['class' => 'time-nice'] );
	}

	public function setEvents( array $events ): self
	{
		$this->events	= $events;
		return $this;
	}

	public function setProjects( array $projects ): self
	{
		$this->projects	= $projects;
		return $this;
	}
}
