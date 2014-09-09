<?php
class Controller_Work_Mission_Future extends Controller_Work_Mission{

	protected $filterKeyPrefix	= 'filter.work.mission.future.';

	protected $defaultFilterValues	= array(
		'tense'			=> 2,
		'states'		=> array(
			Model_Mission::STATUS_NEW,
			Model_Mission::STATUS_ACCEPTED,
			Model_Mission::STATUS_PROGRESS,
			Model_Mission::STATUS_READY
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
		'order'			=> 'dayStart',
		'direction'		=> 'ASC',
	);

	protected function initFilters( $userId ){
		parent::initFilters( $userId );
		$this->logic->generalConditions['dayStart']	= '>='.date( "Y-m-d", time() + 6 * 24 * 60 * 60 );				//  @todo: kriss: calculation is incorrect
	}

	public function ajaxRenderContent(){
		if( $this->session->get( $this->filterKeyPrefix.'mode' ) === 'calendar' )
			$this->redirect( 'work/mission/future/calendar', 'ajaxRenderContent' );
		else
			$this->redirect( 'work/mission/future/list', 'ajaxRenderContent' );
	}
}
?>
