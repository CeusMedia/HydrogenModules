<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Work_Mission_DaysBadge extends Abstraction
{
	protected Logic_Work_Mission $logic;
	protected DateTime $today;
	protected ?Entity_Mission $mission		= NULL;
	protected bool $badgesColored			= TRUE;

	/**
	 *	@param		Environment		$env
	 *	@throws		Exception
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->logic	= Logic_Work_Mission::getInstance( $env );
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->today	= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );
	}

	/**
	 *	@param		int		$days
	 *	@return		string
	 */
	protected function formatDays( int $days ): string
	{
		if( $days > 365.25 )
			return floor( $days / 365.25 )."y";
		if( $days > 30.42 )
			return floor( $days / 30.42 )."m";
		if( $days > 7 )
			return floor( $days / 7 )."w";
		return $days;
	}

	/**
	 *	@param		int			$days
	 *	@param		?string		$class
	 *	@return		string
	 */
	protected function renderBadgeDays( int $days, ?string $class = NULL ): string
	{
		$label	= HtmlTag::create( 'small', $this->formatDays( $days ) );
		$class	= 'badge'.( $class ? ' badge-'.$class : '' );
		return HtmlTag::create( 'span', $label, ['class' => $class] );
	}

	/**
	 *	@param		Entity_Mission	$mission
	 *	@return		string
	 *	@throws		Exception
	 */
	public function renderBadgeDaysOverdue( Entity_Mission $mission ): string
	{
		$end	= max( $mission->dayStart, $mission->dayEnd );										//  use maximum of start and end as due date
		/** @noinspection PhpUnhandledExceptionInspection */
		$diff	= $this->today->diff( new DateTime( $end ) );										//  calculate date difference
		$class	= $this->badgesColored ? "important" : NULL;
		if( $diff->days > 0 && $diff->invert )														//  date is overdue and in past
			return $this->renderBadgeDays( $diff->days, $class );
		return '';																			//  return without content
	}

	/**
	 *	Render overdue container.
	 *	@access		public
	 *	@param		Entity_Mission	$mission		Mission data object
	 *	@return		string		DIV container with number of overdue days or empty string
	 *	@throws		Exception
	 */
	public function renderBadgeDaysStill( Entity_Mission $mission ): string
	{
		if( !$mission->dayEnd || $mission->dayEnd == $mission->dayStart )						//  mission has no duration
			return '';																			//  return without content
		/** @noinspection PhpUnhandledExceptionInspection */
		$start	= new DateTime( $mission->dayStart );
		/** @noinspection PhpUnhandledExceptionInspection */
		$end	= new DateTime( $mission->dayEnd );
		if( $this->today < $start || $end <= $this->today )										//  starts in future or has already ended
			return '';																			//  return without content
		$class	= $this->badgesColored ? "warning" : NULL;
		return $this->renderBadgeDays( $this->today->diff( $end )->days, $class );
	}

	/**
	 *	@param		Entity_Mission	$mission
	 *	@return		string
	 *	@throws		Exception
	 */
	public function renderBadgeDaysUntil( Entity_Mission $mission ): string
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		$start	= new DateTime( $mission->dayStart );
		if( $start <= $this->today )																//  mission has started in past
			return '';																				//  return without content
		$class	= $this->badgesColored ? "success" : NULL;
		return $this->renderBadgeDays( $this->today->diff( $start)->days, $class );
	}

	/**
	 *	@return		string
	 *	@throws		Exception
	 */
	public function render(): string
	{
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

		$iconToday	= HtmlTag::create( 'i', '', ['class' => 'fa fa-exclamation'] );
		return $this->renderBadgeDays( $iconToday, 'important' );
	}

	public function setMission( Entity_Mission $mission ): self
	{
		$this->mission	= $mission;
		return $this;
	}
}
