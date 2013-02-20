<?php
class Logic_Project extends CMF_Hydrogen_Environment_Resource_Logic{

	protected $model;

	public function __construct( $env ){
		parent::__construct( $env );
		$this->model		= new Model_Project( $this->env );                                          //  create projects model
	}

	protected function hasFullAccess(){
		return $this->env->getAcl()->hasFullAccess( $this->env->getSession()->get( 'roleId' ) );
	}

	public function getUserProjects( $userId, $activeOnly = FALSE ){
		if( !$this->hasFullAccess() ){																//  normal access
			$conditions		= $activeOnly ? array( 'status' => array( 0, 1, 2, 3, 4 ) ) : array();	//  ...
			return $this->model->getUserProjects( $userId, $conditions );							//  return user projects
		}
		$userProjects		= array();																//  otherwise create empty project map
		foreach( $this->model->getAll( array(), array( 'title' => 'ASC' ) ) as $project )			//  iterate all projects
			$userProjects[$project->projectId]  = $project;											//  add to projects map
		return $userProjects;																		//  return projects map
	}
}
?>
