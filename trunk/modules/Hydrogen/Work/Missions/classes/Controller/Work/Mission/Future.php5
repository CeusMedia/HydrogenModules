<?php
class Controller_Work_Mission_Future extends Controller_Work_Mission{

	protected $filterKeyPrefix	= 'filter.work.mission.future.';

	protected $defaultFilterValues	= array(
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

	protected function __onInit(){
		parent::__onInit();
		$this->session->set( 'filter.work.mission.mode', 'future' );
	}

	protected function initFilters( $userId ){
		parent::initFilters( $userId );
		$this->logic->generalConditions['dayStart']	= '>='.date( "Y-m-d", time() + 6 * 24 * 60 * 60 );				//  @todo: kriss: calculation is incorrect
	}

	public function ajaxRenderContent(){
		$userId		= $this->session->get( 'userId' );

		//  get list limit and page filters and sanitize them
		$limitMin	= 10;
		$limitMax	= 100;
		$limit		= (int) $this->session->get( $this->filterKeyPrefix.'limit' );
		$limit		= max( $limitMin, min( 100, abs( $limit ) ) );
		$page		= (int) $this->session->get( $this->filterKeyPrefix.'page' );
		$page		= abs( $page );

		//  get all filtered user missions and count them
		$missions	= $this->getFilteredMissions( $userId );
		$total		= count( $missions );

		//  correct page if invalid and cut missions to limit and offset
		if( ( $page * $limit ) >= $total )
			$this->session->set( $this->filterKeyPrefix.'page', $page = 0 );
		$offset		= $page * $limit;
		$missions	= array_slice( $missions, $offset, $limit );

		$this->setData( array(
			'limit'		=> $limit,
			'page'		=> $page,
			'total'		=> $total,
			'missions'	=> $missions,
		) );
		$json	= $this->view->ajaxRenderContent();
//		print( json_encode( $json ) );
//		exit;
	}

	public function index( $missionId = NULL ){
		if( strlen( trim( $missionId ) ) )
			$this->restart( './work/mission/'.$missionId );
		$this->assignFilters();
	}
}
?>
