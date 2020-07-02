<?php
class Model_Module_Page
{
	public function __construct( $env )
	{
		$this->env	= $env;
	}

	public function getByIndices()
	{
		return array();
	}

	public function getAllByIndices( array $conditions = array() ): array
	{
		$scope	= $conditions['scope'] ?: NULL;
		if( !$scope )
			return array();
		$model	= new Model_Menu( $this->env );
//		print_m( $model->getPages( $scope ) );die;
		$pages	= $model->getPages( $scope );
		foreach( $pages as $nr => $page )
			$pages[$nr]->subpages	= array();
		return $pages;
//		foreach( $this->)
		return array();
	}
}
