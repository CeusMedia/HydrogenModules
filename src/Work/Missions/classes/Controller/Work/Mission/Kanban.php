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
class Controller_Work_Mission_Kanban extends Controller_Work_Mission
{
	protected string $filterKeyPrefix		= 'filter.work.mission.kanban.';

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

	/**
	 *	@param		int|string|NULL		$year
	 *	@param		int|string|NULL		$month
	 *	@return		void
	 */
	public function index( int|string|NULL $year = NULL, int|string|NULL $month = NULL ): void
	{
		$this->assignFilters();
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function __onInit(): void
	{
		parent::__onInit();
		$this->session->set( 'filter.work.mission.mode', 'kanban' );

		$this->initFilters( $this->session->get( 'auth_user_id' ) );

		$date	= explode( "-", $this->session->get( $this->filterKeyPrefix.'month' ) );
		$this->setData( [
			'userId'	=> $this->session->get( 'auth_user_id' ),
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

/*	protected function initFilters( string $userId ): void
	{
		parent::initFilters( $userId );
//		$this->logic->generalConditions['...'] = '...';
	}*/
}
