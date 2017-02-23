<?php
/**
 *	View.
 *	@version		$Id$
 */
/**
 *	View.
 *	@version		$Id$
 *	@todo			implement
 *	@todo			code documentation
 */
class View_Work_Mission extends CMF_Hydrogen_View{

	public function help(){
		$topic	= $this->getData( 'topic' );
		if( $topic == "sync" ){
			return $this->loadContentFile( 'html/work/mission/export.html' );
		}
		return "HELP";
	}

	protected function renderNiceTime( $time ){
		list( $hours, $minutes ) = explode( ':', $time );
		return UI_HTML_Tag::create( 'span', array(
			UI_HTML_Tag::create( 'big', str_pad( $hours, 2, 0, STR_PAD_LEFT ) ),
			UI_HTML_Tag::create( 'sup', str_pad( $minutes, 2, 0, STR_PAD_LEFT ) ),
		), array( 'class' => 'time-nice' ) );
	}

	public function ajaxRenderDashboardPanel(){
		$panelId	= $this->getData( 'panelId' );
		switch( $panelId ){
			case 'work-mission-my-today':
			default:
				$words			= $this->getWords();
				$events			= $this->getData( 'events' );
				$userProjects	= $this->getData( 'projects' );
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
				$content	= UI_HTML_Tag::create( 'div', 'Keine Termine.', array( 'alert alert-info' ) );
				if( $events ){
					$rows	= array();
					foreach( $events as $event ){
						$labelProject	= UI_HTML_Tag::create( 'span', $userProjects[$event->projectId]->title, array(
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
				$content	= '<br/>'.$today.'<br/>'.$content.$buttonAdd;
		}
		return $content;
	}

	protected function __onInit(){
		$page			= $this->env->getPage();
		$session		= $this->env->getSession();
		$monthsLong		= array_values( (array) $this->getWords( 'months' ) );
		$monthsShort	= array_values( (array) $this->getWords( 'months-short' ) );

		$page->js->addScript( 'var monthNames = '.json_encode( $monthsLong).';' );
		$page->js->addScript( 'var monthNamesShort = '.json_encode( $monthsShort).';' );

		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsCalendar.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsEditor.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsFilter.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsList.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissions.js' );

/*		$this->config		= $this->env->getConfig();
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
*/	}

	static public function formatSeconds( $duration, $space = ' ' ){
		$seconds 	= $duration % 60;
		$duration	= ( $duration - $seconds ) / 60;
		$minutes	= $duration % 60;
		$duration	= ( $duration - $minutes ) / 60;
		$hours		= $duration % 24;
		$days		= ( $duration - $hours ) / 24;
		$duration	= ( $seconds ? $space.str_pad( $seconds, 2, 0, STR_PAD_LEFT ).'s' : '' );
		$duration	= ( $minutes ? $space.( $hours ? str_pad( $minutes, 2, 0, STR_PAD_LEFT ).'m' : $minutes.'m' ) : '' ).$duration;
		$duration	= ( $hours ? $space.( $days ? str_pad( $hours, 2, 0, STR_PAD_LEFT ).'h' : $hours.'h' ) : '' ).$duration;
		$duration	= ( $days ? $space.$days.'d' : '' ).$duration;
		return ltrim( $duration, $space );
	}

	static public function parseTime( $time ){
		$regexDays	= '@([0-9]+)d\s*@';
		$regexHours	= '@([0-9]+)h\s*@';
		$regexMins	= '@([0-9]+)m\s*@';
		$regexSecs	= '@([0-9]+)s\s*@';
		$seconds	= 0;
		$matches	= array();
		if( preg_match( $regexDays, $time, $matches ) ){
			$time		= preg_replace( $regexDays, '', $time );
			$seconds	+= (int) $matches[1] * 24 * 60 * 60;
		}
		if( preg_match( $regexHours, $time, $matches ) ){
			$time		= preg_replace( $regexHours, '', $time );
			$seconds	+= (int) $matches[1] * 60 * 60;
		}
		if( preg_match( $regexMins, $time, $matches ) ){
			$time		= preg_replace( $regexMins, '', $time );
			$seconds	+= (int) $matches[1] * 60;
		}
		if( preg_match( $regexSecs, $time, $matches ) ){
			$time	= preg_replace( $regexSecs, '', $time );
			$seconds	+= (int) $matches[1];
		}
		return $seconds;
	}

	public function add(){
	}

	public function edit(){
	}

	public function index(){
		$page		= $this->env->getPage();
//		$page->js->addScriptOnReady( 'WorkMissions.init("now");' );			//  @deprecated use Page::runScript instead
		$page->runScript( 'WorkMissions.init("now");', 9 );
	}

	public function remove(){
	}

	public function view(){
		$page			= $this->env->getPage();
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsViewer.js' );
	}
}
?>
