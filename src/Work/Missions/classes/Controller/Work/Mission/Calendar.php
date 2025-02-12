<?php
/**
 *	Controller.
 *	@version		$Id$
 */
/**
 *	Controller.
 *	@todo			implement
 *	@todo			code documentation
 */
class Controller_Work_Mission_Calendar extends Controller_Work_Mission
{
	protected string $filterKeyPrefix		= 'filter.work.mission.calendar.';

	protected array $defaultFilterValues	= [
		'mode'		=> 'now',
		'states'	=> [
			Model_Mission::STATUS_ABORTED,
			Model_Mission::STATUS_REJECTED,
			Model_Mission::STATUS_NEW,
			Model_Mission::STATUS_ACCEPTED,
			Model_Mission::STATUS_PROGRESS,
			Model_Mission::STATUS_READY,
			Model_Mission::STATUS_FINISHED
		],
		'priorities'	=> [
			Model_Mission::PRIORITY_NONE,
			Model_Mission::PRIORITY_HIGHEST,
			Model_Mission::PRIORITY_HIGH,
			Model_Mission::PRIORITY_NORMAL,
			Model_Mission::PRIORITY_LOW,
			Model_Mission::PRIORITY_LOWEST
		],
		'types'			=> [
			Model_Mission::TYPE_TASK,
			Model_Mission::TYPE_EVENT
		],
		'order'			=> 'priority',
		'direction'		=> 'ASC',
	];

	public function index( int|string|NULL $year = NULL, int|string|NULL $month = NULL ): void
	{
		$this->initFilters( $this->userId );
		$this->assignFilters();

/*		if( $year === NULL || $month === NULL ){
			$year	= date( "Y" );
			if( $this->session->has( 'work-mission-view-year' ) )
				$year	= $this->session->get( 'work-mission-view-year' );
			$month	= date( "m" );
			if( $this->session->has( 'work-mission-view-month' ) )
				$month	= $this->session->get( 'work-mission-view-month' );
			$this->restart( './work/mission/calendar/'.$year.'/'.$month );
		}
		if( $month < 1 || $month > 12 ){
			while( $month > 12 ){
				$month	-= 12;
				$year	++;
			}
			while( $month < 1 ){
				$month	+= 12;
				$year	--;
			}
			$this->restart( './work/mission/calendar/'.$year.'/'.$month );
		}
		$this->session->set( 'work-mission-view-year', $year );
		$this->session->set( 'work-mission-view-month', $month );
*/
/*		$this->setData( array(
			'userId'	=> $this->session->get( 'auth_user_id' ),
			'year'		=> $year,
			'month'		=> $month,
		) );
*/
	}

/*	protected function initFilters( string $userId ): void
	{
		parent::initFilters( $userId );
//		$this->logic->generalConditions['...'] = '...';
	}*/

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function __onInit(): void
	{
		parent::__onInit();

		$this->session->set( 'filter.work.mission.mode', 'calendar' );
		if( !$this->session->get( $this->filterKeyPrefix.'month' ) )
			$this->session->set( $this->filterKeyPrefix.'month', date( 'Y' ).'-'.date( 'n' ) );

		$date	= explode( "-", $this->session->get( $this->filterKeyPrefix.'month' ) );
		$this->setData( [
			'userId'	=> $this->userId,
			'year'		=> $date[0],
			'month'		=> $date[1],
		] );
	}

	/**
	 *	@return		void
	 */
	protected function initDefaultFilters(): void
	{
		parent::initDefaultFilters();
		if( !$this->session->get( $this->filterKeyPrefix.'month' ) )
			$this->session->set( $this->filterKeyPrefix.'month', date( "Y" )."-".date( "n" ) );
	}
}
