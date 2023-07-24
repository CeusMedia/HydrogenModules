<?php
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class View_Helper_Work_Mission_List_Days extends View_Helper_Work_Mission_List
{
	protected array $icons		= [];
	protected array $list			= [
		0 => [],
		1 => [],
		2 => [],
		3 => [],
		4 => [],
		5 => [],
		6 => [],
	];

	public function __construct( WebEnvironment $env )
	{
		parent::__construct( $env );
/*		$this->icons	= array(
			'left'		=> HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] ),
			'right'		=> HtmlTag::create( 'i', '', ['class' => 'icon-arrow-right'] ),
			'edit'		=> HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] ),
			'view'		=> HtmlTag::create( 'i', '', ['class' => 'icon-eye-open'] ),
		);*/
	}

	public function countMissions( $day = NULL ): int
	{
		if( $day !== NULL ){
			if( !is_int( $day ) )
				throw new InvalidArgumentException( 'Day must be of integer' );
			if( $day < 0 || $day > 6 )
				throw new OutOfRangeException( 'Day must be at least 0 and at most 6' );
			return count( $this->list[$day] );
		}
		$sum	= 0;
		for( $i=0; $i<7; $i++ )
			if( isset( $this->list[$i] ) )
				$sum	+= count( $this->list[$i] );
		return $sum;
	}

	public function getDayMissions( $day = NULL )
	{
		if( is_int( $day ) && $day >= 0 && $day	< 7 )
			return $this->list[$day];
		return $this->list;
	}

	public function getNearestFallbackDay( $day ): int
	{
		$left	= $right	= (int) $day;
		while( $left >= 0 || $right <= 6 ){
			if( --$left >= 0 && count( $this->list[$left] ) )
				return $left;
			if( ++$right < 7 && count( $this->list[$right] ) )
				return $right;
		}
		return -1;
	}

	public function render(): string
	{
		$list	= [];
		for( $i=0; $i<6; $i++ )
			$list[]		= $this->renderDayList( 1, $i, TRUE, TRUE, FALSE, TRUE );
		return join( $list );
	}

	public function renderDayList( $tense, $day, $showStatus = FALSE, $showPriority = FALSE, $showDate = FALSE, $showActions = FALSE ): string
	{
		$this->missions	= $this->list[$day];
		return parent::renderDayList( $tense, $day, $showStatus, $showPriority, $showDate, $showActions );
	}

	public function setMissions( $missions ): self
	{
		foreach( $missions as $mission ){															//  iterate missions
			/** @noinspection PhpUnhandledExceptionInspection */
			$diff	= $this->today->diff( new DateTime( $mission->dayStart ) );						//  get difference to today
			$days	= $diff->invert ? -1 * $diff->days : $diff->days;								//  calculate days left
			$days	= max( min( $days , 6 ), 0 );													//  restrict to be within 0 and 6
			$this->list[$days][]	= $mission;														//  assign mission to day list
		}
		return $this;
	}
}
