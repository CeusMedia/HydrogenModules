<?php

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_Server extends Controller
{
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Model_Server $modelServer;
	protected Model_Server_Project $modelServerProject;

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		$words	= (object) $this->getWords( 'add' );
		$post	= $this->request->getAllFromSource( 'POST', TRUE );
		if( $post->get( 'add' ) ){
			if( !strlen( trim( $post->get( 'title' ) ) ) )
				$this->env->getMessenger()->noteError( $words->msgTitleMissing );
			else{
				$data	= [
					'status'		=> $post->get( 'status' ),
					'title'			=> $post->get( 'title' ),
					'description'	=> $post->get( 'description' ),
					'createdAt'		=> time(),
				];
				$this->modelServer->add( $data );
				$this->env->getMessenger()->noteSuccess( $words->msgSuccess );
				$this->restart( NULL, TRUE );
			}
		}
		$server	= [];
		foreach( $this->modelServer->getColumns() as $column )
			$server[$column]	= (string) $post->get( $column );
		$this->addData( 'server', (object) $server );
	}

	/**
	 *	@param		string		$serverId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addProject( string $serverId ): void
	{
		$words		= (object) $this->getWords( 'addProject' );
		$data		= [
			'serverId'			=> $serverId,
			'projectId'			=> $this->request->get( 'projectId' ),
			'projectVersionId'	=> $this->request->get( 'projectVersionId' ),
			'status'			=> $this->request->get( 'status' ),
			'title'				=> $this->request->get( 'title' ),
			'description'		=> $this->request->get( 'description' ),
			'createdAt'			=> time(),
		];
		$this->modelServerProject->add( $data );
		$this->messenger->noteSuccess( $words->msgSuccess );
		$this->restart( './admin/server/edit/'.$serverId );
	}

	/**
	 *	@param		string		$serverId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( string $serverId ): void
	{
		$post	= $this->request->getAllFromSource( 'POST', TRUE );
		$words	= (object) $this->getWords( 'edit' );

		if( $post->get( 'edit' ) ){
			if( !strlen( trim( $post->get( 'title' ) ) ) )
				$this->env->getMessenger()->noteError( $words->msgTitleMissing );
			else{
				$data	= [
					'status'		=> $post->get( 'status' ),
					'title'			=> $post->get( 'title' ),
					'description'	=> $post->get( 'description' ),
					'createdAt'		=> time(),
				];
				$this->modelServer->edit( $serverId, $data );
				$this->env->getMessenger()->noteSuccess( $words->msgSuccess );
				$this->restart( NULL, TRUE );
			}
		}
		$modelProject			= new Model_Project( $this->env );
		$modelProjectVersion	= new Model_Project_Version( $this->env );
		$this->addData( 'server', $this->modelServer->get( $serverId ) );
		$this->addData( 'projects', $modelProject->getAll() );
		$relations	= [];
		foreach( $this->modelServerProject->getAllByIndex( 'serverId', $serverId ) as $relation ){
			if( $relation->projectVersionId ){
				$relation->projectVersion	= $modelProjectVersion->get( $relation->projectVersionId );
				$relation->project			= $modelProject->get( $relation->projectVersion->projectId );
			}
			$relations[]	= $relation;
		}
		$this->env->getLanguage()->load( 'admin/project' );
		$this->addData( 'wordsProjectVersionStates', $this->getWords( 'version-states', 'admin/project' ) );
		$this->addData( 'serverProjects', $relations );
	}

	public function filter(): void
	{
	}

	public function index(): void
	{
		$conditions	= [];
		$this->addData( 'servers', $this->modelServer->getAll( $conditions ) );
	}

	/**
	 *	@param		string		$serverId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( string $serverId ): void
	{
		$server	= $this->modelServer->get( $serverId );
		$words	= (object) $this->getWords( 'view' );
		if( !$server )
			$this->env->getMessenger()->noteError( $words->msgErrorInvalidId );
		$this->addData( 'server', $server );
	}

	/**
	 *	@param		string		$serverId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $serverId ): void
	{
		$server	= $this->modelServer->get( $serverId );
		if( $server ){
			$this->modelServerProject->removeByIndex( 'serverId', $serverId );
			$this->modelServer->remove( $serverId );
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		string		$serverProjectId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeProject( string $serverProjectId ): void
	{
		$this->modelServerProject->remove( $serverProjectId );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit(): void
	{
		$this->request				= $this->env->getRequest();
//		$this->session				= $this->env->getSession();
		$this->modelServer			= new Model_Server( $this->env );
		$this->modelServerProject	= new Model_Server_Project( $this->env );
	}
}
