<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Work_Mission_Dashboard_MyEvents extends CMF_Hydrogen_View_Helper_Abstract
{
	protected $events		= [];

	protected $projects		= [];

	public function __construct( $env )
	{
		$this->env		= $env;
	}

	public function render(){
		$words			= $this->env->getLanguage()->getWords( 'work/mission' );
		$today			= HtmlTag::create( 'div', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'small', HtmlTag::create( 'abbr', 'KW', array( 'title' => "Kalenderwoche" ) ) ),
				HtmlTag::create( 'br' ),
				HtmlTag::create( 'span', (int) date( 'W' ), array(
					'style' => 'font-size: 2em;'
				) ),
			), array( 'style' => 'text-align: center; float: right; width: 50px' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'span', $words['days'][date( 'w' )], array( 'style' => 'font-size: 1.8em' ) ),
				HtmlTag::create( 'br' ),
				HtmlTag::create( 'span', date( 'd' ).'. '.$words['months'][date( 'n' )].' '.date( 'Y' ), array(
					'style' => 'font-size: 1.1em'
				) ),
			), array( 'style' => 'text-align: center' ) ),
		) );
		$content	= HtmlTag::create( 'div', 'Keine Termine.', array( 'class' => 'alert alert-info' ) );
		if( $this->events ){
			$rows	= [];
			foreach( $this->events as $event ){
				$project		= $this->projects[$event->projectId];
				$labelProject	= HtmlTag::create( 'span', $project->title, array(
					'style'		=> 'font-size: smaller'
				) );
				$link			= HtmlTag::create( 'a', $event->title, array(
					'href'		=> './work/mission/view/'.$event->missionId,
					'style'		=> 'font-size: larger'
				) );
				$label	= $link.'<br/>'.$labelProject;
				$rows[]	= HtmlTag::create( 'tr', array(
					HtmlTag::create( 'td', $this->renderNiceTime( $event->timeStart ).'<br/><small class="muted">'.$this->renderNiceTime( $event->timeEnd ).'</small>' ),
					HtmlTag::create( 'td', $label, array( 'class' => 'autocut' ) ),
//							HtmlTag::create( 'td', '#'.$event->priority ),
				) );
			};
			$colgroup	= HtmlElements::ColumnGroup( array(
				'50px',
//						'20px',
				'',
			) );
			$tbody		= HtmlTag::create( 'tbody', $rows );
			$content	= HtmlTag::create( 'table', $colgroup.$tbody, array(
				'class'	=> 'table table-condensed table-fixed'
			) );
		}
		$buttonAdd	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-plus"></i>&nbsp;neuer Termin', array(
			'href'	=> './work/mission/add?type=1',
			'class'	=> 'btn btn-block btn-success',
		) );
		return '<br/>'.$today.'<br/>'.$content.$buttonAdd;
	}

	protected function renderNiceTime( $time ){
		if( !strlen( trim( $time ) ) )
			return '-';
		list( $hours, $minutes ) = explode( ':', $time );
		return HtmlTag::create( 'span', array(
			HtmlTag::create( 'big', str_pad( $hours, 2, 0, STR_PAD_LEFT ) ),
			HtmlTag::create( 'sup', str_pad( $minutes, 2, 0, STR_PAD_LEFT ) ),
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
