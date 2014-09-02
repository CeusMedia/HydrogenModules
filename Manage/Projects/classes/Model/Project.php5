<?php
class Model_Project extends CMF_Hydrogen_Model{
	protected $name			= 'projects';
	protected $columns		= array(
		'projectId',
		'parentId',
		'status',
		'url',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'projectId';
	protected $indices		= array(
		'parentId',
		'status',
		'title',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function getUserProjects( $userId, $conditions = array() ){
		$modelProject	= new Model_Project( $this->env );
		$modelRelation	= new Model_Project_User( $this->env );
		$projectIds	= array();
		foreach( $modelRelation->getAllByIndex( 'userId', $userId ) as $relation )
			$projectIds[]	= $relation->projectId;
		if( !$projectIds )
			return array();
		$conditions['projectId']	= $projectIds;
		$projects	= array();
		$orders		= array( 'title' => 'ASC' );
		foreach( $modelProject->getAll( $conditions, $orders ) as $project )
			$projects[$project->projectId]	= $project;
		return $projects;
	}

	public function getProjectUsers( $projectId, $conditions = array() ){
		$modelUser	= new Model_User( $this->env );
		$modelRelation	= new Model_Project_User( $this->env );
		$userIds	= array();
		foreach( $modelRelation->getAllByIndex( 'projectId', $projectId ) as $relation )
			$userIds[]	= $relation->userId;
		if( !$userIds )
			return array();
		$conditions['userId']	= $userIds;
		$orders		= array( 'roleId' => 'ASC', 'username' => 'ASC' );
		$users		= array();
		foreach( $modelUser->getAll( $conditions, $orders ) as $user )
			$users[$user->userId]	= $user;
		return $users;
	}
}
?>