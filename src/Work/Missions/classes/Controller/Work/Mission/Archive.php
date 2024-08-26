<?php
class Controller_Work_Mission_Archive extends Controller_Work_Mission
{
	protected string $filterKeyPrefix	= 'filter.work.mission.archive.';

	protected array $defaultFilterValues	= [
		'states'		=> [
			Model_Mission::STATUS_ABORTED,
			Model_Mission::STATUS_REJECTED,
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
		'order'			=> 'dayStart',
		'direction'		=> 'ASC',
	];

	/**
	 *	@param		int|string|NULL		$missionId
	 *	@return		void
	 */
	public function index( int|string $missionId = NULL ): void
	{
		if( strlen( trim( $missionId ?? '' ) ) )
			$this->restart( './work/mission/'.$missionId );
		$this->initFilters( $this->userId );
		$this->assignFilters();
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
		$this->session->set( 'filter.work.mission.mode', 'archive' );
	}
}
