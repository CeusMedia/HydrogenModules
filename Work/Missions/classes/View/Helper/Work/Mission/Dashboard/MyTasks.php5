<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Work_Mission_Dashboard_MyTasks extends CMF_Hydrogen_View_Helper_Abstract{

	protected $projects		= [];
	protected $tasks		= [];
	protected $rowStyles	= array(
		33 => 'warning',
		66 => 'error',
	);

	public function __construct( $env ){
		$this->env		= $env;
	}

	public function render(){
		if( !count( $this->projects ) )
//			throw new RuntimeException( 'No user projects set or available' );
			return HtmlTag::create( 'div', 'Keine Projekte vorhanden.', array( 'class' => 'alert alert-info' ) );

//		$words			= $this->getWords();
		$showLimit			= 10;
		$userProjects		= $this->projects;
		$content			= HtmlTag::create( 'div', 'Keine Termine.', array( 'class' => 'alert alert-info' ) );
		$helperDaysBadge	= new View_Helper_Work_Mission_DaysBadge( $this->env );
		$count				= count( $this->tasks );
		$tasks				= array_slice( $this->tasks, 0, $showLimit );
		if( $this->tasks ){
			$rows	= [];
			foreach( $tasks as $task ){
				$project			= $this->projects[$task->projectId];
				$daysLeft			= ( strtotime( $task->dayEnd ) - strtotime( date( 'Y-m-d' ) ) ) / ( 24 * 3600 );
				$daysLeft			= max( 1, min( 6, $daysLeft ) );
				$priorityTask		= $task->priority ? $task->priority : 6;
				$priorityProject	= $project->priority ? $project->priority : 6;
				$priority			= 3 / ( $priorityTask + $priorityProject + $daysLeft );
				$rowStyle	= '';
				$helperDaysBadge->setMission( $task );
				foreach( $this->rowStyles as $edge => $style )
					if( $priority * 100 > $edge )
						$rowStyle	= $style;
				$labelProject	= HtmlTag::create( 'span', $project->title, array(
					'style'		=> 'font-size: smaller'
				) );
				$link			= HtmlTag::create( 'a', $task->title, array(
					'href'		=> './work/mission/view/'.$task->missionId,
				) );
				$label	= $link/*.'<br/>'.$labelProject*/;
				$key	= $priority.uniqid();
				$daysBadge	= HtmlTag::create( 'span', $helperDaysBadge->render(), array( 'class' => 'pull-right' ) );
				$rows[$key]	= HtmlTag::create( 'tr', array(
					HtmlTag::create( 'td', $label, array( 'class' => 'autocut' ) ),
					HtmlTag::create( 'td', $daysBadge ),
//					HtmlTag::create( 'td', '<small class="muted">'.round( $priority, 2 ).'</small>' );
				), array( 'class' => $rowStyle ) );
			};
			krsort( $rows );
			$colgroup	= UI_HTML_Elements::ColumnGroup( array(
				'',
				'50px',
			) );
			$tbody		= HtmlTag::create( 'tbody', $rows );
			$content	= HtmlTag::create( 'table', $colgroup.$tbody, array(
				'class'	=> 'table table-condensed table-fixed'
			) );
			if( $count > $showLimit ){
				$content	.= HtmlTag::create( 'div', 'Und '.( $count - $showLimit ).' Weitere.', array( 'class' => 'alert alert-info' ) );
			}
		}
		$buttonAdd	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-plus"></i>&nbsp;neue Aufgabe', array(
			'href'	=> './work/mission/add?type=0',
			'class'	=> 'btn btn-block btn-success',
		) );
		$content	= $content.$buttonAdd;
		return $content;
	}

	public function setProjects( $projects ){
		$this->projects	= $projects;
	}

	public function setTasks( $tasks ){
		$this->tasks	= $tasks;
	}
}
?>
