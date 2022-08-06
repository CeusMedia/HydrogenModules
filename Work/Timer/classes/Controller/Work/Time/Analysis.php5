<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Time_Analysis extends Controller
{
	protected $request;
	protected $session;
	protected $userId;
	protected $modelProject;
	protected $modelMission;
	protected $modelTimer;
	protected $logicProject;
	protected $projectMap;
	protected $filterPrefix		= 'filter_work_timer_analysis_';

	public function filter( $reset = NULL )
	{
		if( $reset ){
			$setFilters	= $this->session->getAll( $this->filterPrefix );
			foreach( array_keys( $setFilters ) as $key )
				$this->session->remove( $this->filterPrefix.$key );
		}
		if( $this->request->has( 'mode' ) )
			$this->session->set( $this->filterPrefix.'mode', $this->request->get( 'mode' ) );
		if( $this->request->has( 'duration' ) )
			$this->session->set( $this->filterPrefix.'duration', $this->request->get( 'duration' ) );
		if( $this->request->has( 'durationFrom' ) )
			$this->session->set( $this->filterPrefix.'durationFrom', $this->request->get( 'durationFrom' ) );
		if( $this->request->has( 'durationTo' ) )
			$this->session->set( $this->filterPrefix.'durationTo', $this->request->get( 'durationTo' ) );
		if( $this->request->has( 'year' ) )
			$this->session->set( $this->filterPrefix.'year', $this->request->get( 'year' ) );
		if( $this->request->has( 'month' ) )
			$this->session->set( $this->filterPrefix.'month', $this->request->get( 'month' ) );
		if( $this->request->has( 'week' ) )
			$this->session->set( $this->filterPrefix.'week', $this->request->get( 'week' ) );
		if( $this->request->has( 'userId' ) )
			$this->session->set( $this->filterPrefix.'userId', $this->request->get( 'userId' ) );

		if( $this->request->has( 'projectIds' ) ){
			$projectIds	= [];
			foreach( $this->request->get( 'projectIds' ) as $projectId )
				if( $projectId )
					$projectIds[]	= $projectId;
			$this->session->set( $this->filterPrefix.'projectIds', $projectIds );
		}
		if( $this->request->has( 'userIds' ) ){
			$userIds	= [];
			foreach( $this->request->get( 'userIds' ) as $userId )
				if( $userId )
					$userIds[]	= $userId;
			$this->session->set( $this->filterPrefix.'userIds', $userIds );
		}

		$this->restart( NULL, TRUE );
	}

	public function index()
	{
		$filterProjectIds	= $this->session->get( $this->filterPrefix.'projectIds' );
		$filterUserIds		= $this->session->get( $this->filterPrefix.'userIds' );
		$filterMode			= $this->session->get( $this->filterPrefix.'mode' );
		$filterDuration		= $this->session->get( $this->filterPrefix.'duration' );
		$filterDurationFrom	= $this->session->get( $this->filterPrefix.'durationFrom' );
		$filterDurationTo	= $this->session->get( $this->filterPrefix.'durationTo' );
		$filterYear			= $this->session->get( $this->filterPrefix.'year' );
		$filterMonth		= $this->session->get( $this->filterPrefix.'month' );
		$filterWeek			= $this->session->get( $this->filterPrefix.'week' );

		$data		= [];
		$users		= [];
		$projects	= [];

		$userMap		= Logic_Authentication::getInstance( $this->env )->getRelatedUsers( $this->userId );

		$timestampStart	= 0;
		$timestampEnd	= 0;
		if( $filterDuration === "duration" ){
			if( $filterDurationFrom && $filterDurationTo ){
				$timestampStart	= strtotime( min( $filterDurationFrom, $filterDurationTo ) );
				$timestampEnd	= strtotime( min( $filterDurationFrom, $filterDurationTo ) ) + 24 * 3600;
			}
		}
		else if( $filterDuration === "year" ){
			$timestampStart	= strtotime( $filterYear.'-01-01' );
			$timestampEnd	= strtotime( $filterYear.'-12-31' );
		}
		else if( $filterDuration === "month" ){
			$lastDayInMonth	= date("t", strtotime( $filterYear.'-'.$filterMonth.'-01' ) );
			$timestampStart	= strtotime( $filterYear.'-'.$filterMonth.'-01' );
			$timestampEnd	= strtotime( $filterYear.'-'.$filterMonth.'-'.$lastDayInMonth );
		}
		else if( $filterDuration === "week" ){
			$timestampStart	= strtotime( sprintf( '%dW%02d', $filterYear, $filterWeek ) );
			$timestampEnd	= $timestampStart + 7 * 24 * 3600 - 1;
		}
		if( $filterMode === 'projects' ){
			if( $filterProjectIds ){
				$sumPlanned	= 0;
				$sumNeeded	= 0;
				$users	= $this->logicProject->getProjectsUsers( $filterProjectIds );
				foreach( $users as $userId => $user ){
					if( !array_key_exists( $userId, $userMap ) )
						continue;
					$conditions	= array(
						'status'	=> 3,
						'workerId'	=> $userId,
						'projectId'	=> $filterProjectIds,
					);
					if( $timestampStart && $timestampEnd )
						$conditions['modifiedAt']	= '>< '.$timestampStart.' & '.$timestampEnd;
					$sums				= $this->sumTimers( $conditions );
					$sumPlanned			+= $sums->secondsPlanned;
					$sumNeeded			+= $sums->secondsNeeded;
					if( !$sums->secondsPlanned && !$sums->secondsNeeded )
						continue;
					$data[$userId]		= $sums;
				}
				$data['@total']	= (object) array(
					'secondsPlanned'	=> $sumPlanned,
					'secondsNeeded'		=> $sumNeeded,
				);
				$this->addData( 'projectsUsers', $users );
			}
		}
		else if( $filterMode === 'users' ){
			if( $filterUserIds ){
				$sumPlanned	= 0;
				$sumNeeded	= 0;
				$usersProjects	= $this->logicProject->getUsersProjects( $filterUserIds );
				foreach( $usersProjects as $projectId => $project ){
					if( !array_key_exists( $projectId, $this->projectMap ) )
						continue;
					if( !in_array( $project->status, array( 0, 1, 2 ) ) )
						continue;

					$conditions	= array(
						'workerId'	=> $filterUserIds,
						'projectId'	=> $projectId,
					);
					if( $timestampStart && $timestampEnd )
						$conditions['modifiedAt']	= '>< '.$timestampStart.' & '.$timestampEnd;
					$sums				= $this->sumTimers( $conditions );
					$sumPlanned			+= $sums->secondsPlanned;
					$sumNeeded			+= $sums->secondsNeeded;
					$data[$projectId]	= $sums;
				}
				$data['@total']	= (object) array(
					'secondsPlanned'	=> $sumPlanned,
					'secondsNeeded'		=> $sumNeeded,
				);
				$this->addData( 'usersProjects', $usersProjects );
			}
		}
/*		else if( $filterMode === 'user' ){
			$this->addData( 'filterUserId', $this->session->get( $this->filterPrefix.'userId' ) );
		}*/

		$this->addData( 'data', $data );
		$this->addData( 'allProjects', $this->projectMap );
		$this->addData( 'allUsers', $userMap );
		$this->addData( 'filterProjectIds', $filterProjectIds );
		$this->addData( 'filterUserIds', $this->session->get( $this->filterPrefix.'userIds' ) );
		$this->addData( 'filterMode', $this->session->get( $this->filterPrefix.'mode' ) );
		$this->addData( 'filterDuration', $this->session->get( $this->filterPrefix.'duration' ) );
		$this->addData( 'filterDurationFrom', $this->session->get( $this->filterPrefix.'durationFrom' ) );
		$this->addData( 'filterDurationTo', $this->session->get( $this->filterPrefix.'durationTo' ) );
		$this->addData( 'filterYear', $this->session->get( $this->filterPrefix.'year' ) );
		$this->addData( 'filterMonth', $this->session->get( $this->filterPrefix.'month' ) );
		$this->addData( 'filterWeek', $this->session->get( $this->filterPrefix.'week' ) );
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->userId			= $this->session->get( 'auth_user_id' );
		$this->modelProject		= new Model_Project( $this->env );
		$this->modelMission		= new Model_Mission( $this->env );
		$this->modelTimer		= new Model_Work_Timer( $this->env );
		$this->logicProject		= Logic_Project::getInstance( $this->env );
		$this->projectMap		= $this->logicProject->getUserProjects( $this->userId, TRUE );
//		$this->addData( 'filterProjectId', $this->session->get( 'filter_work_timer_projectId' ) );
//		$this->addData( 'filterStatus', (int) $this->session->get( 'filter_work_timer_status' ) );
		$this->addData( 'userId', $this->userId );

		if( !$this->session->get( $this->filterPrefix.'durationFrom' ) )
			$this->session->set( $this->filterPrefix.'durationFrom', date( "Y-m-d" ) );
		if( !$this->session->get( $this->filterPrefix.'durationTo' ) )
			$this->session->set( $this->filterPrefix.'durationTo', date( "Y-m-d" ) );

		if( !$this->session->get( $this->filterPrefix.'year' ) )
			$this->session->set( $this->filterPrefix.'year', date( "Y" ) );
		if( !$this->session->get( $this->filterPrefix.'month' ) )
			$this->session->set( $this->filterPrefix.'month', date( "m" ) );
		if( !$this->session->get( $this->filterPrefix.'week' ) )
			$this->session->set( $this->filterPrefix.'week', date( "W" ) );
		if( !$this->session->get( $this->filterPrefix.'mode' ) )
			$this->session->set( $this->filterPrefix.'mode', 'projects' );
	}

	protected function sumTimers( array $conditions )
	{
		$sumPlanned	= 0;
		$sumNeeded	= 0;
		$timers		= $this->modelTimer->getAll( $conditions );
		foreach( $timers as $timer ){
			$sumPlanned	+= $timer->secondsPlanned;
			$sumNeeded	+= $timer->secondsNeeded;
		}
		return (object) array(
			'secondsPlanned'	=> $sumPlanned,
			'secondsNeeded'		=> $sumNeeded,
			'timers'			=> $timers,
		);
	}
}
