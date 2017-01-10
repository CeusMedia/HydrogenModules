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
class Controller_Work_Mission_Kanban extends Controller_Work_Mission{

	protected $filterKeyPrefix	= 'filter.work.mission.kanban.';

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
		$this->session->set( 'filter.work.mission.mode', 'kanban' );

		$date	= explode( "-", $this->session->get( $this->filterKeyPrefix.'month' ) );
		$this->setData( array(
			'userId'	=> $this->session->get( 'userId' ),
			'year'		=> $date[0],
			'month'		=> $date[1],
		) );
	}

	public function ajaxRenderIndex(){
		$userId	= $this->getData( 'userId' );
		$this->addData( 'users', $this->userMap );
	}

	public function ajaxSetMissionStatus(){
		$missionId	= $this->request->get( 'missionId' );
		$status		= (int) $this->request->get( 'status' );

		try{
			if( !$missionId )
				throw new InvalidArgumentException( 'Mission ID is missing' );
			if( !in_array( $status, array( 0, 1, 2, 3 ) ) )
				throw new InvalidArgumentException( 'Invalid status given' );
			$mission	= $this->model->get( $missionId );
			if( !$mission )
				throw new InvalidArgumentException( 'Invalid mission ID given' );
			$responseStatus	= FALSE;
			if( $mission->status != $status ){
				$data	= array(
					'status'		=> $status,
					'modifiedAt'	=> time(),
				);
//				if( $status === 1 )
//					$data['workerId']	= $this->userId;
				$this->model->edit( $missionId, $data );
				$this->logic->noteChange( 'update', $missionId, $mission, $this->userId );
				$responseStatus		= TRUE;
				$mission	= $this->model->get( $missionId );
			}
			print json_encode( array(
				'status'	=> $responseStatus,
				'item'		=> $mission,
			) );
		}
		catch( Exception $e ){
			header( "HTTP/1.1 400 OK" );
			print( json_encode( $e->getMessage() ) );
		}
		exit;
	}

	protected function initDefaultFilters(){
		parent::initDefaultFilters();
		if( !$this->session->get( $this->filterKeyPrefix.'month' ) )
			$this->session->set( $this->filterKeyPrefix.'month', date( "Y" )."-".date( "n" ) );
	}

	public function index( $year = NULL, $month = NULL ){
		$this->assignFilters();
	}

	protected function initFilters( $userId ){
		parent::initFilters( $userId );
//		$this->logic->generalConditions['...'] = '...';
	}
}
?>
