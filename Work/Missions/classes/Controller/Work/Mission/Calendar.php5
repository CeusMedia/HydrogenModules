<?php
/**
 *	Controller.
 *	@version		$Id$
 */
/**
 *	Controller.
 *	@version		$Id$
 *	@todo			implement
 *	@todo			code documentation
 */
class Controller_Work_Mission_Calendar extends Controller_Work_Mission{

	protected $filterKeyPrefix	= 'filter.work.mission.calendar.';

	protected $defaultFilterValues	= array(
		'states'	=> array(
			Model_Mission::STATUS_ABORTED,
			Model_Mission::STATUS_REJECTED,
			Model_Mission::STATUS_NEW,
			Model_Mission::STATUS_ACCEPTED,
			Model_Mission::STATUS_PROGRESS,
			Model_Mission::STATUS_READY,
			Model_Mission::STATUS_FINISHED
		),
		'priorities'	=> array(
			Model_Mission::PRIORITY_NONE,
			Model_Mission::PRIORITY_HIGHEST,
			Model_Mission::PRIORITY_HIGH,
			Model_Mission::PRIORITY_NORMAL,
			Model_Mission::PRIORITY_LOW,
			Model_Mission::PRIORITY_LOWEST
		),
		'types'			=> array(
			Model_Mission::TYPE_TASK,
			Model_Mission::TYPE_EVENT
		),
		'order'			=> 'priority',
		'direction'		=> 'ASC',
	);

	protected function __onInit(){
		parent::__onInit();

		$this->session->set( 'filter.work.mission.mode', 'calendar' );

		$date	= explode( "-", $this->session->get( $this->filterKeyPrefix.'month' ) );
		$this->setData( array(
			'userId'	=> $this->userId,
			'year'		=> $date[0],
			'month'		=> $date[1],
		) );
	}

	public function ajaxRenderIndex(){
		$userId	= $this->getData( 'userId' );
	}

	protected function initDefaultFilters(){
		parent::initDefaultFilters();
		if( !$this->session->get( $this->filterKeyPrefix.'month' ) )
			$this->session->set( $this->filterKeyPrefix.'month', date( "Y" )."-".date( "n" ) );
	}

	public function index( $year = NULL, $month = NULL ){
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
			'userId'	=> $this->session->get( 'userId' ),
			'year'		=> $year,
			'month'		=> $month,
		) );
*/
	}

	protected function initFilters( $userId ){
		parent::initFilters( $userId );
//		$this->logic->generalConditions['...'] = '...';
	}
}
?>
