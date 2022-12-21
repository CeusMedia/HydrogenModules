<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Admin_Project extends Controller
{
	public function add()
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= $this->env->getLanguage()->getWords( 'admin/project' );

		$title			= $request->get( 'title' );
		$description		= $request->get( 'description' );
		$status			= $request->get( 'status' );
		$model			= new Model_Project( $this->env );
		if( $request->get( 'doAdd' ) )
		{
			if( empty( $title ) )
				$messenger->noteError( $words['add']['msgErrorTitleEmpty'] );
			else
			{
				if( $model->getAll( ['title' => $title] ) )
					$messenger->noteError( $words['add']['msgErrorTitleNotUnique'], $title );
				else
				{
					$data	= array(
						'title'		=> $title,
						'description'	=> $description,
						'status'	=> $status,
						'createdAt'	=> time(),
					);
					$model->add( $data );
					$messenger->noteSuccess( $words['add']['msgSuccess'], $title );
					$this->restart( 'admin/project' );
				}
			}
		}
		$this->view->addData( 'title', $title );
		$this->view->addData( 'description', $description );
		$this->view->addData( 'status', $status );
	}

	public function addVersion( $projectId ){
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'addVersion' );
		$model			= new Model_Project_Version( $this->env );
		$data	= array(
			'projectId'	=> $projectId,
			'status'	=> $request->get( 'status' ),
			'version'	=> $request->get( 'version' ),
			'title'		=> $request->get( 'title' ),
			'description'	=> $request->get( 'description' ),
			'createdAt'	=> time(),
		);
		$model->add( $data );
		$messenger->noteSuccess( $words->msgSuccess );
		$this->restart( './admin/project/edit/'.$projectId );
	}

	public function ajaxGetVersions( $projectId ){
		$modelVersion	= new Model_Project_Version( $this->env );
		$versions		= $modelVersion->getAllByIndex( 'projectId', $projectId );
		print( json_encode( $versions ) );
		exit;
	}

	public function edit( $projectId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'edit' );

		$model			= new Model_Project( $this->env );
		$project		= $model->get( $projectId );
		if( !$project ){
			$messenger->noteError( $words->msgErrorInvalidId );
			$this->restart( './admin/project' );
		}

		if( $request->get( 'doEdit' ) )
		{
			$title			= $request->get( 'title' );
			if( empty( $title ) )
				$messenger->noteError( $words->msgErrorTitleEmpty );
			else
			{
				if( $model->getAll( ['title' => $title, 'projectId' => '!= '.$projectId] ) )
					$messenger->noteError( $words->msgErrorTitleNotUnique, $title );
				else
				{
					$data	= array(
						'title'			=> $title,
						'description'	=> $request->get( 'description' ),
						'status'		=> $request->get( 'status' ),
						'modifiedAt'	=> time(),
					);
					$messenger->noteSuccess( $words->msgSuccess, $title );
					$this->restart( './admin/project' );
				}
			}
		}
		$modelVersion	= new Model_Project_Version( $this->env );
		$versions		= $modelVersion->getAllByIndex( 'projectId', $project->projectId );#
		$data			= array(
			'projectId'	=> $project->projectId,
			'project'	=> $project,
			'versions'	=> $versions,
		);
		$this->view->setData( $data );
	}

	public function filter()
	{
		$this->env->getMessenger()->noteSuccess( "Tests have been filtered." );
		$this->restart( 'test/table' );
	}

	public function index()
	{
		$modelProject	= new Model_Project( $this->env );
		$modelVersion	= new Model_Project_Version( $this->env );
		$projects	= $modelProject->getAll();
		foreach( $projects as $project ){
			$indices	= ['projectId' => $project->projectId, 'status' => 1];
			$project->version	= $modelVersion->getByIndices( $indices );
		}
		$this->view->addData( 'projects', $projects );
	}

	public function remove( $projectId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'remove' );

		$modelProject	= new Model_Project( $this->env );
		$project		= $modelProject->get( $projectId );
		if( !$project ){
			$messenger->noteError( $words->msgErrorInvalidId );
			$this->restart( './admin/project/edit/'.$projectId );
		}

		$modelVersion	= new Model_Project_Version( $this->env );
		$modelVersion->removeByIndex( 'projectId', $projectId );
		$modelProject->remove( $projectId );

		$messenger->noteSuccess( $words->msgSuccess, $project->title );
		$this->restart( './admin/project' );
	}

	public function removeVersion( $versionId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();

		$model			= new Model_Project_Version( $this->env );
		$version		= $model->get( $versionId );
		$model->remove( $versionId );
		$this->restart( './admin/project/edit/'.$version->projectId );
	}
}
?>
