<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Project extends Model
{
//	const STATES_ACTIVE		= [];

	protected string $name			= 'projects';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'projectId';

	protected array $indices		= [
		'creatorId',
		'parentId',
		'status',
		'priority',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;

	public function getUserProjects( $userId, array $conditions = [], array $orders = [] ): array
	{
		$modelProject	= new Model_Project( $this->env );
		$modelRelation	= new Model_Project_User( $this->env );
		$projectIds		= [];
		$defaultProject	= 0;
		foreach( $modelRelation->getAllByIndex( 'userId', $userId ) as $relation ){
			$defaultProject	= $relation->isDefault ? $relation->projectId : $defaultProject;
			$projectIds[]	= $relation->projectId;
		}
		if( !$projectIds )
			return [];
		$conditions['projectId']	= $projectIds;
		$orders		= $orders ? $orders : ['title' => 'ASC'];
		$projects	= [];
		foreach( $modelProject->getAll( $conditions, $orders ) as $project ){
			$project->isDefault = $defaultProject == $project->projectId;
			$projects[$project->projectId]	= $project;
		}
		return $projects;
	}

	public function getProjectUsers( $projectId, array $conditions = [], array $orders = [] ): array
	{
		$modelUser		= new Model_User( $this->env );
		$modelRelation	= new Model_Project_User( $this->env );
		$userIds		= [];
		foreach( $modelRelation->getAllByIndex( 'projectId', $projectId ) as $relation )
			$userIds[]	= $relation->userId;
		if( !$userIds )
			return [];
		$conditions['userId']	= $userIds;
		$orders		= $orders ? $orders : [/*'roleId' => 'ASC', */'username' => 'ASC'];
		$users		= [];
		foreach( $modelUser->getAll( $conditions, $orders ) as $user ){
			unset( $user->password );
			$users[$user->userId]	= $user;
		}
		return $users;
	}
}
