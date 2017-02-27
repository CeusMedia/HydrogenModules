<?php
class View_Helper_Work_Mission_DaysBadge extends CMF_Hydrogen_View_Helper_Abstract{

	protected $badgesColored	= TRUE;

	public function __construct( $env ){
		$this->env			= $env;
		$this->logic		= Logic_Work_Mission::getInstance( $env );
		$this->today		= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );
	}

	protected function formatDays( $days ){
		if( $days > 365.25 )
			return floor( $days / 365.25 )."y";
		if( $days > 30.42 )
			return floor( $days / 30.42 )."m";
		if( $days > 7 )
			return floor( $days / 7 )."w";
		return $days;
	}

	protected function renderBadgeDays( $days, $class = NULL ){
		$label	= UI_HTML_Tag::create( 'small', $this->formatDays( $days ) );
		$class	= 'badge'.( $class ? ' badge-'.$class : '' );
		return UI_HTML_Tag::create( 'span', $label, array( 'class' => $class ) );
	}

	public function renderBadgeDaysOverdue( $mission ){
		$end	= max( $mission->dayStart, $mission->dayEnd );										//  use maximum of start and end as due date
		$diff	= $this->today->diff( new DateTime( $end ) );										//  calculate date difference
		$class	= $this->badgesColored ? "important" : NULL;
		if( $diff->days > 0 && $diff->invert )														//  date is overdue and in past
			return $this->renderBadgeDays( $diff->days, $class );
	}

	/**
	 *	Render overdue container.
	 *	@access		public
	 *	@param		object		$mission		Mission data object
	 *	@return		string		DIV container with number of overdue days or empty string
	 */
	public function renderBadgeDaysStill( $mission ){
		if( !$mission->dayEnd || $mission->dayEnd == $mission->dayStart )						//  mission has no duration
			return "";																			//  return without content
		$start	= new DateTime( $mission->dayStart );
		$end	= new DateTime( $mission->dayEnd );
		if( $this->today < $start || $end <= $this->today )										//  starts in future or has already ended
			return "";																			//  return without content
		$class	= $this->badgesColored ? "warning" : NULL;
		return $this->renderBadgeDays( $this->today->diff( $end )->days, $class );
	}

	public function renderBadgeDaysUntil( $mission ){
		$start	= new DateTime( $mission->dayStart );
		if( $start <= $this->today )																//  mission has started in past
			return "";																			//  return without content
		$class	= $this->badgesColored ? "success" : NULL;
		return $this->renderBadgeDays( $this->today->diff( $start)->days, $class );
	}

	public function render(){
		$todayStart	= strtotime( date( 'Y-m-d', time() ) );
		$todayEnd	= strtotime( date( 'Y-m-d', time() ) ) + 24 * 3600 - 1;
		$missionStart	= strtotime( $this->mission->dayStart );
		$missionEnd		= strtotime( $this->mission->dayEnd ) + 24 * 3600 - 1;
		if( $missionStart > $todayStart )
			return $this->renderBadgeDaysUntil( $this->mission );
		if( $missionEnd < $todayStart )
			return $this->renderBadgeDaysOverdue( $this->mission );
		if( $missionEnd > $todayEnd )
			return $this->renderBadgeDaysStill( $this->mission );

		$iconToday	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-exclamation' ) );
		return $this->renderBadgeDays( $iconToday, 'important' );
	}

	public function setMission( $mission ){
		$this->mission	= $mission;
	}
}
