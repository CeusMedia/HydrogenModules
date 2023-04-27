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

	public function ajaxRenderIndex(): void
	{
		$userId	= $this->getData( 'userId' );
		$this->addData( 'users', $this->userMap );
	}

	public function ajaxSetMissionStatus(): void
	{
		$missionId	= $this->request->get( 'missionId' );
		$status		= (int) $this->request->get( 'status' );

		try{
			if( !$missionId )
				throw new InvalidArgumentException( 'Mission ID is missing' );
			if( !in_array( $status, [0, 1, 2, 3] ) )
				throw new InvalidArgumentException( 'Invalid status given' );
			$mission	= $this->model->get( $missionId );
			if( !$mission )
				throw new InvalidArgumentException( 'Invalid mission ID given' );
			$responseStatus	= FALSE;
			if( $mission->status != $status ){
				$data	= [
					'status'		=> $status,
					'modifiedAt'	=> time(),
				];
//				if( $status === 1 )
//					$data['workerId']	= $this->userId;
				$this->model->edit( $missionId, $data );
				$this->logic->noteChange( 'update', $missionId, $mission, $this->userId );
				$responseStatus		= TRUE;
				$mission	= $this->model->get( $missionId );
			}
			print json_encode( [
				'status'	=> $responseStatus,
				'item'		=> $mission,
			] );
		}
		catch( Exception $e ){
			header( "HTTP/1.1 400 OK" );
			print( json_encode( $e->getMessage() ) );
		}
		exit;
	}

	public function index( $year = NULL, $month = NULL ): void
	{
		$this->assignFilters();
	}

	protected function initDefaultFilters(): void
	{
		parent::initDefaultFilters();
		if( !$this->session->get( $this->filterKeyPrefix.'month' ) )
			$this->session->set( $this->filterKeyPrefix.'month', date( "Y" )."-".date( "n" ) );
	}

	protected function initFilters( string $userId ): void
	{
		parent::initFilters( $userId );
//		$this->logic->generalConditions['...'] = '...';
	}
}
