<?php
class Logic_Mission{

	public $timeOffset			= 0; # 4 hours night shift: 14400;
	public $generalConditions	= array();
	public $model;
	public $useProjects			= FALSE;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env			= $env;
		$this->model		= new Model_Mission( $env );
		$this->useProjects	= $this->env->getModules()->has( 'Manage_Projects' );
	}

	public function getFilterConditions( $sessionFilterKeyPrefix, $additionalConditions = array() ){
		$session	= $this->env->getSession();
		$query		= $session->get( $sessionFilterKeyPrefix.'query' );
		$types		= $session->get( $sessionFilterKeyPrefix.'types' );
		$priorities	= $session->get( $sessionFilterKeyPrefix.'priorities' );
		$states		= $session->get( $sessionFilterKeyPrefix.'states' );
		$projects	= $session->get( $sessionFilterKeyPrefix.'projects' );
		$direction	= $session->get( $sessionFilterKeyPrefix.'direction' );
		$order		= $session->get( $sessionFilterKeyPrefix.'order' );
		$orders		= array(					//  collect order pairs
			$order		=> $direction,			//  selected or default order and direction
			'timeStart'	=> 'ASC',				//  order events by start time
		);
		$conditions	= array();
		if( is_array( $types ) && count( $types ) )
			$conditions['type']	= $types;
		if( is_array( $priorities ) && count( $priorities ) )
			$conditions['priority']	= $priorities;
		if( is_array( $states ) && count( $states ) )
			$conditions['status']	= $states;
		if( strlen( $query ) )
			$conditions['title']	= '%'.str_replace( array( '*', '?' ), '%', $query ).'%';
		if( is_array( $projects ) && count( $projects ) )											//  if filtered by projects
			$conditions['projectId']	= $projects;												//  apply project conditions
		foreach( $additionalConditions as $key => $value )
			$conditions[$key]			= $value;
		return $conditions;
	}

	public function getUserProjects( $userId, $activeOnly = FALSE ){
		if( !$this->useProjects )																	//  projects module not enabled
			return array();																			//  return empty map
		$modelProject	= new Model_Project( $this->env );											//  create projects model
		if( !$this->hasFullAccess() ){																//  normal access
			$conditions		= $activeOnly ? array( 'status' => array( 0, 1, 2 ) ) : array();		//  ...
			return $modelProject->getUserProjects( $userId, $conditions );							//  return user projects
		}
		$userProjects	= array();																	//  otherwise create empty project map
		foreach( $modelProject->getAll( array(), array( 'title' => 'ASC' ) ) as $project )			//  iterate all projects
			$userProjects[$project->projectId]	= $project;											//  add to projects map
		return $userProjects;																		//  return projects map
	}

	public function getUserMissions( $userId, $conditions = array(), $orders = array(), $limits = NULL ){
		$conditions	= array_merge( $this->generalConditions, $conditions );
		$orders		= $orders ? $orders : array( 'dayStart' => 'ASC' );

		if( $this->hasFullAccess() )																//  user has full access
			return $this->model->getAll( $conditions, $orders, $limits );							//  return all missions matched by conditions

		$havings	= array(																		//  additional conditions
			'ownerId = '.(int) $userId,																//  user is owner
			'workerId = '.(int) $userId,															//  or user is worker
		);
		if( $this->useProjects ){																	//  projects module is enabled
			$userProjects	= array_keys( $this->getUserProjects( $userId, TRUE ) );				//  get user projects from model
			if( $userProjects )																		//  user has projects
				$havings[]	= 'projectId IN ('.join( ',', $userProjects ).')';						//  add projects condition
			array_unshift( $userProjects, 0 );														//  
			if( isset( $conditions['projectId'] ) )													//  projects have been selected
				$userProjects	= array_intersect( $conditions['projectId'], $userProjects );		//  intersect user projectes and selected projects
			$conditions['projectId']	= $userProjects;											//  
		}
		$groupings	= array( 'missionId' );															//  HAVING needs grouping
		$havings	= array( join( ' OR ', $havings ) );											//  combine havings with OR
		return $this->model->getAll( $conditions, $orders, $limits, NULL, $groupings, $havings );	//  return missions matched by conditions
	}

	public function getDate( $string ){
		$day	= 24 * 60 * 60;
		$now	= time();
		$string	= strtolower( trim( $string ) );

		if( preg_match( "/^[+-][0-9]+$/", $string ) ){
			$sign	= substr( $string, 0, 1 );
			$number	= substr( $string, 1 );
			$time	= $sign == '+' ? $now + $number * $day : $now - $number * $day;
		}
		else{
			switch( $string ){
				case '':
				case 'heute':
					$time	= $now;
					break;
				case '+1':
				case 'morgen':
					$time	= $now + 1 * $day;
					break;
				case '+2':
				case 'übermorgen':
					$time	= $now + 1 * $day;
					break;
				default:
					$time	= strtotime( $string );
					break;
			}
		}
		return date( "Y-m-d", $time );
	}

	protected function hasFullAccess(){
		return $this->env->getAcl()->hasFullAccess( $this->env->getSession()->get( 'roleId' ) );
	}

	public function moveDate(){}

	public function noteChange( $type, $missionId, $data, $currentUserId ){
		$model	= new Model_Mission_Change( $this->env );
		if( !$model->count( array( 'missionId' => $missionId ) ) ){
			$model->add( array(
				'missionId'		=> $missionId,
				'userId'		=> $currentUserId,
				'type'			=> $type,
				'data'			=> serialize( $data ),
				'timestamp'		=> time()
			), FALSE );
		}
	}
}
?>
