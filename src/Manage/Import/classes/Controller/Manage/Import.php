<?php

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Import extends Controller
{
	protected HttpRequest $request;
	protected Model_Import_Connection $modelConnection;
	protected Model_Import_Connector $modelConnector;
	protected array $connectorMap		= [];

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		if( $this->request->getMethod()->isPost() ){
			$this->modelConnection->add( [
				'importConnectorId'	=> $this->request->get( 'importConnectorId' ),
//				'creatorId'			=> $this->localUserId,
				'status'			=> $this->request->get( 'status' ),
				'hostName'			=> $this->request->get( 'hostName' ),
				'hostPort'			=> $this->request->get( 'hostPort' ),
				'hostPath'			=> $this->request->get( 'hostPath' ),
				'authType'			=> $this->request->get( 'authType' ),
				'authUsername'		=> $this->request->get( 'authUsername' ),
				'authPassword'		=> $this->request->get( 'authPassword' ),
				'authKey'			=> $this->request->get( 'authKey' ),
				'title'				=> $this->request->get( 'title' ),
				'description'		=> $this->request->get( 'description' ),
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			] );
			$this->restart( NULL, TRUE );
		}
	}

	/**
	 * @param		string		$connectionId
	 * @return		void
	 * @throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( string $connectionId ): void
	{
		$connection	= $this->modelConnection->get( $connectionId );
		if( NULL === $connection ){
			$this->env->getMessenger()->noteError( 'Invalid Connection ID' );
			$this->restart( NULL, TRUE );
		}

		if( $this->request->getMethod()->isPost() ){
			$this->modelConnection->edit( $connectionId, [
				'importConnectorId'	=> $this->request->get( 'importConnectorId' ),
				'status'			=> $this->request->get( 'status' ),
				'hostName'			=> $this->request->get( 'hostName' ),
				'hostPort'			=> $this->request->get( 'hostPort' ),
				'hostPath'			=> $this->request->get( 'hostPath' ),
				'authType'			=> $this->request->get( 'authType' ),
				'authUsername'		=> $this->request->get( 'authUsername' ),
				'authPassword'		=> $this->request->get( 'authPassword' ),
				'authKey'			=> $this->request->get( 'authKey' ),
				'title'				=> $this->request->get( 'title' ),
				'description'		=> $this->request->get( 'description' ),
				'modifiedAt'		=> time(),
			] );
			$this->restart( NULL, TRUE );
		}
		$connection	= $this->modelConnection->get( $connectionId );
		$this->addData( 'connection', $connection );
	}

	public function index(): void
	{
		$connections	= $this->modelConnection->getAll();
		$this->addData( 'connections', $connections );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->modelConnector	= new Model_Import_Connector( $this->env );
		$this->modelConnection	= new Model_Import_Connection( $this->env );

		$connectors	= $this->modelConnector->getAll();
		foreach( $connectors as $connector )
			$this->connectorMap[$connector->importConnectorId]	= $connector;
		$this->addData( 'connectorMap', $this->connectorMap );
	}
}
