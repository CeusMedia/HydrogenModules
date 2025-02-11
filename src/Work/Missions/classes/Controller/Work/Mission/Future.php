<?php
class Controller_Work_Mission_Future extends Controller_Work_Mission
{
	protected string $filterKeyPrefix		= 'filter.work.mission.future.';

	protected array $defaultFilterValues	= [
		'states'		=> [
			Model_Mission::STATUS_NEW,
			Model_Mission::STATUS_ACCEPTED,
			Model_Mission::STATUS_PROGRESS,
			Model_Mission::STATUS_READY
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
		'order'			=> 'dayStart',
		'direction'		=> 'ASC',
	];

	/**
	 *	@param		int|string|NULL		$missionId
	 *	@return		void
	 */
	public function index( int|string|NULL $missionId = NULL ): void
	{
		if( strlen( trim( $missionId ) ) )
			$this->restart( './work/mission/'.$missionId );
		$this->initFilters( $this->userId );
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
		$this->session->set( 'filter.work.mission.mode', 'future' );
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		void
	 */
	protected function initFilters( int|string $userId ): void
	{
		parent::initFilters( $userId );
		$this->logic->generalConditions['dayStart']	= '>= '.date( "Y-m-d", time() + 6 * 24 * 60 * 60 );				//  @todo:  calculation is incorrect
	}
}
