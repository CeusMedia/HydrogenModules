<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Work_Mission_Dashboard_MyTasks extends Abstraction
{
	protected array $projects		= [];
	protected array $tasks		= [];
	protected array $rowStyles	= [
		33 => 'warning',
		66 => 'error',
	];

	public function __construct( Environment $env )
	{
		$this->env		= $env;
	}

	public function render(): string
	{
		if( !count( $this->projects ) )
//			throw new RuntimeException( 'No user projects set or available' );
			return HtmlTag::create( 'div', 'Keine Projekte vorhanden.', ['class' => 'alert alert-info'] );

//		$words			= $this->getWords();
		$showLimit			= 10;
		$userProjects		= $this->projects;
		$content			= HtmlTag::create( 'div', 'Keine Termine.', ['class' => 'alert alert-info'] );
		$helperDaysBadge	= new View_Helper_Work_Mission_DaysBadge( $this->env );
		$count				= count( $this->tasks );
		$tasks				= array_slice( $this->tasks, 0, $showLimit );
		if( $this->tasks ){
			$rows	= [];
			foreach( $tasks as $task ){
				$project			= $this->projects[$task->projectId];
				$daysLeft			= ( strtotime( $task->dayEnd ) - strtotime( date( 'Y-m-d' ) ) ) / ( 24 * 3600 );
				$daysLeft			= max( 1, min( 6, $daysLeft ) );
				$priorityTask		= $task->priority ?: 6;
				$priorityProject	= $project->priority ?: 6;
				$priority			= 3 / ( $priorityTask + $priorityProject + $daysLeft );
				$rowStyle	= '';
				$helperDaysBadge->setMission( $task );
				foreach( $this->rowStyles as $edge => $style )
					if( $priority * 100 > $edge )
						$rowStyle	= $style;
				$labelProject	= HtmlTag::create( 'span', $project->title, [
					'style'		=> 'font-size: smaller'
				] );
				$link			= HtmlTag::create( 'a', $task->title, [
					'href'		=> './work/mission/view/'.$task->missionId,
				] );
				$label	= $link/*.'<br/>'.$labelProject*/;
				$key	= $priority.uniqid();
				$daysBadge	= HtmlTag::create( 'span', $helperDaysBadge->render(), ['class' => 'pull-right'] );
				$rows[$key]	= HtmlTag::create( 'tr', [
					HtmlTag::create( 'td', $label, ['class' => 'autocut'] ),
					HtmlTag::create( 'td', $daysBadge ),
//					HtmlTag::create( 'td', '<small class="muted">'.round( $priority, 2 ).'</small>' );
				], ['class' => $rowStyle] );
			};
			krsort( $rows );
			$colgroup	= HtmlElements::ColumnGroup( [
				'',
				'50px',
			] );
			$tbody		= HtmlTag::create( 'tbody', $rows );
			$content	= HtmlTag::create( 'table', $colgroup.$tbody, [
				'class'	=> 'table table-condensed table-fixed'
			] );
			if( $count > $showLimit ){
				$content	.= HtmlTag::create( 'div', 'Und '.( $count - $showLimit ).' Weitere.', ['class' => 'alert alert-info'] );
			}
		}
		$buttonAdd	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-plus"></i>&nbsp;neue Aufgabe', [
			'href'	=> './work/mission/add?type=0',
			'class'	=> 'btn btn-block btn-success',
		] );
		$content	= $content.$buttonAdd;
		return $content;
	}

	public function setProjects( array $projects ): self
	{
		$this->projects	= $projects;
		return $this;
	}

	public function setTasks( array $tasks ): self
	{
		$this->tasks	= $tasks;
		return $this;
	}
}
