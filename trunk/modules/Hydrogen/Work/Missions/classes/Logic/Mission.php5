<?php
class Logic_Mission{

	public $timeOffset	= 14400;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env			= $env;
		$this->model		= new Model_Mission( $env );
		$this->useProjects	= $this->env->getModules()->has( 'Manage_Projects' );

	}

	public function getUserProjects( $userId ){
		if( !$this->useProjects )																	//  projects module not enabled
			return array();																			//  return empty map
		$modelProject	= new Model_Project( $this->env );											//  create projects model
		if( !$this->hasFullAccess() )																//  normal access
			return $modelProject->getUserProjects( $userId );										//  return user projects
		$userProjects	= array();																	//  otherwise create empty project map
		foreach( $modelProject->getAll( array(), array( 'title' => 'ASC' ) ) as $project )			//  iterate all projects
			$userProjects[$project->projectId]	= $project;											//  add to projects map
		return $userProjects;																		//  return projects map
	}

	public function getUserMissions( $userId, $conditions = array(), $orders = array(), $limits = NULL ){
		$orders	= $orders ? $orders : array( 'dayStart' => 'ASC' );

		if( $this->hasFullAccess() )																//  user has full access
			return $this->model->getAll( $conditions, $orders, $limits );							//  return all missions matched by conditions

		$havings	= array(																		//  additional conditions
			'ownerId = '.(int) $userId,																//  user is owner
			'workerId = '.(int) $userId,															//  or user is worker
		);
		if( $this->useProjects ){																	//  projects module is enabled
			$userProjects	= $this->getUserProjects( $userId );									//  get user projects from model
			if( $userProjects )																		//  user has projects
				$havings[]	= 'projectId IN ('.join( ',', array_keys( $userProjects ) ).')';		//  add projects condition
		}
		$groupings	= array( 'missionId' );															//  HAVING needs grouping
		$havings	= array( join( ' OR ', $havings ) );											//  combine havings with OR
		return $this->model->getAll( $conditions, $orders, $limits, NULL, $groupings, $havings );	//  return modules matched by conditions
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
