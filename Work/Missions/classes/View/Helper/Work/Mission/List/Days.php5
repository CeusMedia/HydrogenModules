<?php
class View_Helper_Work_Mission_List_Days extends View_Helper_Work_Mission_List{

	protected $icons		= array();
	protected $list			= array(
		0 => array(),
		1 => array(),
		2 => array(),
		3 => array(),
		4 => array(),
		5 => array(),
		6 => array(),
	);

	public function __construct( $env ){
		parent::__construct( $env );
/*		$this->icons	= array(
			'left'		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) ),
			'right'		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-right' ) ),
			'edit'		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) ),
			'view'		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-open' ) ),
		);*/
	}

	public function countMissions( $day = NULL ){
		if( $day !== NULL ){
			if( !is_int( $day ) )
				throw new InvalidArgumentException( 'Day must be of integer' );
			if( $day < 0 || $day > 6 )
				throw new OutOfRangeException( 'Day must be atleast 0 and atmost 6' );
			return count( $this->list[$day] );
		}
		$sum	= 0;
		for( $i=0; $i<7; $i++ )
			if( isset( $this->list[$i] ) )
				$sum	+= count( $this->list[$i] );
		return $sum;
	}

	public function getDayMissions( $day = NULL ){
		if( is_int( $day ) && $day >= 0 && $day	< 7 )
			return $this->list[$day];
		return $this->list;
	}

	public function getNearestFallbackDay( $day ){
		$left	= $right	= (int) $day;
		while( $left >= 0 || $right <= 6 ){
			if( --$left >= 0 && count( $this->list[$left] ) )
				return $left;
			if( ++$right < 7 && count( $this->list[$right] ) )
				return $right;
		}
		return -1;
	}

	public function render(){
		$list	= array();
		for( $i=0; $i<6; $i++ )
			$list[]		= $this->renderDayList( 1, $i, TRUE, TRUE, FALSE, TRUE );
		return join( $list );
	}

	public function renderDayList( $tense, $day, $showStatus = FALSE, $showPriority = FALSE, $showDate = FALSE, $showActions = FALSE ){
		$this->missions	= $this->list[$day];
		return parent::renderDayList( $tense, $day, $showStatus, $showPriority, $showDate, $showActions );
	}

	public function setMissions( $missions ){
		foreach( $missions as $mission ){															//  iterate missions
			$diff	= $this->today->diff( new DateTime( $mission->dayStart ) );						//  get difference to today
			$days	= $diff->invert ? -1 * $diff->days : $diff->days;								//  calculate days left
			$days	= max( min( $days , 6 ), 0 );													//  restrict to be within 0 and 6
			$this->list[$days][]	= $mission;														//  assign mission to day list
		}
	}
}
?>
