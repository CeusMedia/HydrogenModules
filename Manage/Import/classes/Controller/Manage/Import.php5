<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Import extends Controller
{
	protected $request;
	protected $modelConnection;
	protected $modelConnector;
	protected $connectorMap		= [];

	public function add()
	{
		if( $this->request->getMethod()->isPost() ){
			$this->modelConnection->add( array(
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
			) );
			$this->redirect( NULL, TRUE );
		}
	}

	public function edit( $connectionId )
	{
		if( $this->request->getMethod()->isPost() ){
			$this->modelConnection->edit( $connectionId, array(
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
			) );
			$this->redirect( NULL, TRUE );
		}
		$connection	= $this->modelConnection->get( $connectionId );
		$this->addData( 'connection', $connection );
	}

	public function index()
	{
		$connections	= $this->modelConnection->getAll();
		$this->addData( 'connections', $connections );
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
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
