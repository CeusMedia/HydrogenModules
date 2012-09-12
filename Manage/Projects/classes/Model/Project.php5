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
		$list			= array();
		foreach( $modelRelation->getAllByIndex( 'userId', $userId ) as $relation )
			$list[$relation->projectId]	= NULL;
		$conditions['projectId']	= array_keys( $list );
		$projects		= $modelProject->getAll( $conditions, array( 'title' => 'ASC' ) );
		foreach( $projects as $project )
			$list[$project->projectId]	= $project;
		return $list;
	}
}
?>