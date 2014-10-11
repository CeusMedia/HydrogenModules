<?php
class Controller_Work_Mission_Archive extends Controller_Work_Mission{

	protected $filterKeyPrefix	= 'filter.work.mission.archive.';
	protected $options;
	protected $request;

	protected $defaultFilterValues	= array(
		'states'		=> array(
			Model_Mission::STATUS_ABORTED,
			Model_Mission::STATUS_REJECTED,
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
		'order'			=> 'dayStart',
		'direction'		=> 'DESC',
	);

	protected function __onInit(){
		parent::__onInit();
		$this->session->set( 'filter.work.mission.mode', 'archive' );
	}

	public function ajaxRenderIndex(){
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
//		$json	= $this->view->ajaxRenderIndex();
//		print( json_encode( $json ) );
//		exit;
	}

	public function index( $missionId = NULL ){
		if( strlen( trim( $missionId ) ) )
			$this->restart( './work/mission/'.$missionId );
		$this->assignFilters();
	}

	protected function initFilters( $userId ){
		parent::initFilters( $userId );
//		$this->logic->generalConditions['...'] = '...';
	}
}
?>
