<?php
class View_Helper_Work_Mission_Dashboard_MyEvents extends CMF_Hydrogen_View_Helper_Abstract{

	protected $events		= array();
	protected $projects		= array();

	public function __construct( $env ){
		$this->env		= $env;
	}

	public function render(){
		$words			= $this->env->getLanguage()->getWords( 'work/mission' );
		$today			= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'small', UI_HTML_Tag::create( 'abbr', 'KW', array( 'title' => "Kalenderwoche" ) ) ),
				UI_HTML_Tag::create( 'br' ),
				UI_HTML_Tag::create( 'span', (int) date( 'W' ), array(
					'style' => 'font-size: 2em;'
				) ),
			), array( 'style' => 'text-align: center; float: right; width: 50px' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'span', $words['days'][date( 'w' )], array( 'style' => 'font-size: 1.8em' ) ),
				UI_HTML_Tag::create( 'br' ),
				UI_HTML_Tag::create( 'span', date( 'd' ).'. '.$words['months'][date( 'n' )].' '.date( 'Y' ), array(
					'style' => 'font-size: 1.1em'
				) ),
			), array( 'style' => 'text-align: center' ) ),
		) );
		$content	= UI_HTML_Tag::create( 'div', 'Keine Termine.', array( 'class' => 'alert alert-info' ) );
		if( $this->events ){
			$rows	= array();
			foreach( $this->events as $event ){
				$project		= $this->projects[$event->projectId];
				$labelProject	= UI_HTML_Tag::create( 'span', $project->title, array(
					'style'		=> 'font-size: smaller'
				) );
				$link			= UI_HTML_Tag::create( 'a', $event->title, array(
					'href'		=> './work/mission/view/'.$event->missionId,
					'style'		=> 'font-size: larger'
				) );
				$label	= $link.'<br/>'.$labelProject;
				$rows[]	= UI_HTML_Tag::create( 'tr', array(
					UI_HTML_Tag::create( 'td', $this->renderNiceTime( $event->timeStart ).'<br/><small class="muted">'.$this->renderNiceTime( $event->timeEnd ).'</small>' ),
					UI_HTML_Tag::create( 'td', $label, array( 'class' => 'autocut' ) ),
//							UI_HTML_Tag::create( 'td', '#'.$event->priority ),
				) );
			};
			$colgroup	= UI_HTML_Elements::ColumnGroup( array(
				'50px',
//						'20px',
				'',
			) );
			$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
			$content	= UI_HTML_Tag::create( 'table', $colgroup.$tbody, array(
				'class'	=> 'table table-condensed table-fixed'
			) );
		}
		$buttonAdd	= UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-plus"></i>&nbsp;neuer Termin', array(
			'href'	=> './work/mission/add?type=1',
			'class'	=> 'btn btn-block btn-success',
		) );
		return '<br/>'.$today.'<br/>'.$content.$buttonAdd;
	}

	protected function renderNiceTime( $time ){
		if( !strlen( trim( $time ) ) )
			return '-';
		list( $hours, $minutes ) = explode( ':', $time );
		return UI_HTML_Tag::create( 'span', array(
			UI_HTML_Tag::create( 'big', str_pad( $hours, 2, 0, STR_PAD_LEFT ) ),
			UI_HTML_Tag::create( 'sup', str_pad( $minutes, 2, 0, STR_PAD_LEFT ) ),
		), array( 'class' => 'time-nice' ) );
	}

	public function setEvents( $events ){
		$this->events	= $events;
	}

	public function setProjects( $projects ){
		$this->projects	= $projects;
	}
}
?>
