<?php
class Model_Project extends CMF_Hydrogen_Model{

//	const STATES_ACTIVE		= [];

	protected $name			= 'projects';

	protected $columns		= array(
		'projectId',
		'creatorId',
		'parentId',
		'status',
		'priority',
		'url',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'projectId';

	protected $indices		= array(
		'creatorId',
		'parentId',
		'status',
		'priority',
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;

	public function getUserProjects( $userId, $conditions = [], $orders = [] ){
		$modelProject	= new Model_Project( $this->env );
		$modelRelation	= new Model_Project_User( $this->env );
		$projectIds		= [];
		$defaultProject	= 0;
		foreach( $modelRelation->getAllByIndex( 'userId', $userId ) as $relation ){
			$defaultProject	= $relation->isDefault ? $relation->projectId : $defaultProject;
			$projectIds[]	= $relation->projectId;
		}
		if( !$projectIds )
			return array();
		$conditions['projectId']	= $projectIds;
		$orders		= $orders ? $orders : array( 'title' => 'ASC' );
		$projects	= [];
		foreach( $modelProject->getAll( $conditions, $orders ) as $project ){
			$project->isDefault = $defaultProject == $project->projectId;
			$projects[$project->projectId]	= $project;
		}
		return $projects;
	}

	public function getProjectUsers( $projectId, $conditions = [], $orders = [] ){
		$modelUser		= new Model_User( $this->env );
		$modelRelation	= new Model_Project_User( $this->env );
		$userIds		= [];
		foreach( $modelRelation->getAllByIndex( 'projectId', $projectId ) as $relation )
			$userIds[]	= $relation->userId;
		if( !$userIds )
			return array();
		$conditions['userId']	= $userIds;
		$orders		= $orders ? $orders : array( /*'roleId' => 'ASC', */'username' => 'ASC' );
		$users		= [];
		foreach( $modelUser->getAll( $conditions, $orders ) as $user ){
			unset( $user->password );
			$users[$user->userId]	= $user;
		}
		return $users;
	}
}
?>
