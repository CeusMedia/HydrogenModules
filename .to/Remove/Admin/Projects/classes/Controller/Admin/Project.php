<?php

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_Project extends Controller
{
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Model_Project $modelProject;
	protected Model_Project_Version $modelVersion;

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		$words			= $this->env->getLanguage()->getWords( 'admin/project' );

		$title			= $this->request->get( 'title' );
		$description	= $this->request->get( 'description' );
		$status			= $this->request->get( 'status' );
#		if( $this->request->get( 'doAdd' ) )
		{
			if( empty( $title ) )
				$this->messenger->noteError( $words['add']['msgErrorTitleEmpty'] );
			else
			{
				if( $this->modelProject->getAll( ['title' => $title] ) )
					$this->messenger->noteError( $words['add']['msgErrorTitleNotUnique'], $title );
				else{
					$data	= [
						'title'			=> $title,
						'description'	=> $description,
						'status'		=> $status,
						'createdAt'		=> time(),
					];
					$this->modelProject->add( $data );
					$this->messenger->noteSuccess( $words['add']['msgSuccess'], $title );
					$this->restart( 'admin/project' );
				}
			}
		}
		$this->view->addData( 'title', $title );
		$this->view->addData( 'description', $description );
		$this->view->addData( 'status', $status );
	}

	/**
	 *	@param		int|string		$projectId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addVersion( int|string $projectId ): void
	{
		$words			= (object) $this->getWords( 'addVersion' );
		$data	= [
			'projectId'		=> $projectId,
			'status'		=> $this->request->get( 'status' ),
			'version'		=> $this->request->get( 'version' ),
			'title'			=> $this->request->get( 'title' ),
			'description'	=> $this->request->get( 'description' ),
			'createdAt'		=> time(),
		];
		$this->modelProject->add( $data );
		$this->messenger->noteSuccess( $words->msgSuccess );
		$this->restart( './admin/project/edit/'.$projectId );
	}

	public function ajaxGetVersions( int|string $projectId ): never
	{
		$versions		= $this->modelVersion->getAllByIndex( 'projectId', $projectId );
		print( json_encode( $versions ) );
		exit;
	}

	/**
	 *	@param		int|string		$projectId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $projectId ): void
	{
		$words			= (object) $this->getWords( 'edit' );
		$project		= $this->modelProject->get( $projectId );
		if( !$project ){
			$this->messenger->noteError( $words->msgErrorInvalidId );
			$this->restart( './admin/project' );
		}

		if( $this->request->get( 'doEdit' ) ) {
			$title			= $this->request->get( 'title' );
			if( empty( $title ) )
				$this->messenger->noteError( $words->msgErrorTitleEmpty );
			else{
				if( $this->modelProject->getAll( ['title' => $title, 'projectId' => '!= '.$projectId] ) )
					$this->messenger->noteError( $words->msgErrorTitleNotUnique, $title );
				else{
					$data	= [
						'title'			=> $title,
						'description'	=> $this->request->get( 'description' ),
						'status'		=> $this->request->get( 'status' ),
						'modifiedAt'	=> time(),
					];
					$this->modelProject->edit( $projectId, $data );
					$this->messenger->noteSuccess( $words->msgSuccess, $title );
					$this->restart( './admin/project' );
				}
			}
		}
		$versions		= $this->modelVersion->getAllByIndex( 'projectId', $project->projectId );
		$data			= [
			'projectId'	=> $project->projectId,
			'project'	=> $project,
			'versions'	=> $versions,
		];
		$this->view->setData( $data );
	}

	public function filter(): void
	{
		$this->messenger->noteSuccess( "Tests have been filtered." );
		$this->restart( 'test/table' );
	}

	public function index(): void
	{
		$projects	= $this->modelProject->getAll();
		foreach( $projects as $project ){
			$indices	= ['projectId' => $project->projectId, 'status' => 1];
			$project->version	= $this->modelVersion->getByIndices( $indices );
		}
		$this->view->addData( 'projects', $projects );
	}

	/**
	 *	@param		int|string		$projectId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $projectId ): void
	{
		$words			= (object) $this->getWords( 'remove' );

		$project		= $this->modelProject->get( $projectId );
		if( !$project ){
			$this->messenger->noteError( $words->msgErrorInvalidId );
			$this->restart( './admin/project/edit/'.$projectId );
		}

		$this->modelVersion->removeByIndex( 'projectId', $projectId );
		$this->modelProject->remove( $projectId );

		$this->messenger->noteSuccess( $words->msgSuccess, $project->title );
		$this->restart( './admin/project' );
	}

	/**
	 *	@param		int|string		$versionId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeVersion( int|string $versionId ): void
	{
		$version		= $this->modelVersion->get( $versionId );
		if( $version )
			$this->modelVersion->remove( $versionId );
		$this->restart( './admin/project/edit/'.$version->projectId );
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->modelProject		= new Model_Project( $this->env );
		$this->modelVersion		= new Model_Project_Version( $this->env );
	}
}
