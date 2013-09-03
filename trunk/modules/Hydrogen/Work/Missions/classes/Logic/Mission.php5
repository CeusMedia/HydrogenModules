<?php
class Logic_Mission{

	public $timeOffset	= 0; # 4 hours night shift: 14400;
	public $generalConditions	= array();

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env			= $env;
		$this->model		= new Model_Mission( $env );
		$this->useProjects	= $this->env->getModules()->has( 'Manage_Projects' );
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
//print_m( $conditions );
		$conditions	= array_merge( $this->generalConditions, $conditions );
//print_m( $conditions );
//die;
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
}
?>
