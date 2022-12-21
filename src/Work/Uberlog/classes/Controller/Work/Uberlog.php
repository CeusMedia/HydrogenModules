<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Uberlog extends Controller
{
	/**	@var	Model_Log_Record		$model		Instance of log record model */
	protected Model_Log_Record $modelRecord;

	public function ajaxUpdateIndex()
	{
		$lastId	= $this->env->getRequest()->get( 'lastId' );
		$filters	= ['logRecordId' => '> '.$lastId];
		$orders		= ['logRecordId' => 'ASC'];
		print( json_encode( $this->listRecords( $filters, $orders ) ) );
		exit;
	}

	public function index()
	{
		$records	= $this->listRecords();
		$this->addData( 'records', $records );
	}

	public function record()
	{
		$request	= $this->env->getRequest();
		$post		= $request->getAllFromSource( 'POST', TRUE );
		$data		= $post->getAll();
		$data['timestamp']	= $post->has( 'timestamp' ) ? $post->get( 'timestamp' ) : time();
		$data['logCategoryId']	= $this->getCategoryId( $post->get( 'category' ) );
		$data['logClientId']	= $this->getClientId( $post->get( 'client' ) );
		$data['logHostId']		= $this->getHostId( $post->get( 'host' ) );
		$data['logUserAgentId']	= $this->getUserAgentId( $post->get( 'userAgent' ) );
		$recordId	= $this->modelRecord->add( $data );
		print( $recordId );
		exit;
		if( $request->isAjax() ){
			print( json_encode( $recordId ) );
			exit;
		}
		$this->restart( NULL, TRUE );
	}

	public function remove( $recordId )
	{
		$request	= $this->env->getRequest();
		$this->modelRecord->remove( $recordId );
		$this->env->getMessenger()->noteSuccess( 'Record '.$recordId.' has been removed.' );
		if( $request->isAjax() )
			exit;
		$this->restart( NULL, TRUE );
	}

	public function testRecord( $type = 0 )
	{
		$data		= array(
			'category'	=> 'test',
			'message'	=> 'Test',
			'timestamp'	=> time(),
			'type'		=> $type,
		);
		$response	= $this->env->get( 'uberlog' )->report( $data );
		$this->restart( NULL, TRUE );

#		if( !$this->env->getModules()->has( 'Resource_Uberlog' ) )
#			throw new RuntimeException( 'Module "Resource:Uberlog" is not installed' );
#		$this->env->get( 'uberlog' )->report( $data );

	}

	public function view()
	{
	}

	protected function __onInit(): void
	{
		$this->modelRecord	= new Model_Log_Record( $this->env );
	}

	protected function getCategoryId( $categoryName )
	{
		if( !strlen( trim( $categoryName ) ) )
			return 0;
		$modelCategory	= new Model_Log_Category( $this->env );
		$category		= $modelCategory->getByIndex( 'title', $categoryName );
		if( $category ){
			$modelCategory->edit( $category->logCategoryId, array( 'loggedAt' => time() ) );
			return $category->logCategoryId;
		}
		$data		= array(
			'title'		=> $categoryName,
			'createdAt'	=> time()
		);
		return $modelCategory->add( $data );
	}

	protected function getClientId( $clientName )
	{
		if( !strlen( trim( $clientName ) ) )
			return 0;
		$modelClient	= new Model_Log_Client( $this->env );
		$client			= $modelClient->getByIndex( 'title', $clientName );
		if( $client ){
			$modelClient->edit( $client->logClientId, array( 'loggedAt' => time() ) );
			return $client->logClientId;
		}
		$data		= array(
			'title'		=> $clientName,
			'createdAt'	=> time()
		);
		return $modelClient->add( $data );
	}

	protected function getHostId( $hostName )
	{
		if( !strlen( trim( $hostName ) ) )
			return 0;
		$modelHost	= new Model_Log_Host( $this->env );
		$host		= $modelHost->getByIndex( 'title', $hostName );
		if( $host ){
			$modelHost->edit( $host->logHostId, array( 'loggedAt' => time() ) );
			return $host->logHostId;
		}
		$data		= array(
			'title'	=> $hostName,
			'createdAt'	=> time()
		);
		return $modelHost->add( $data );
	}

	protected function getUserAgentId( $userAgent )
	{
		if( !strlen( trim( $userAgent ) ) )
			return 0;
		$modelAgent	= new Model_Log_UserAgent( $this->env );
		$agent		= $modelAgent->getByIndex( 'title', $userAgent );
		if( $agent ){
			$modelAgent->edit( $agent->logUserAgentId, array( 'loggedAt' => time() ) );
			return $agent->logUserAgentId;
		}
		$data		= array(
			'title'	=> $userAgent,
			'createdAt'	=> time()
		);
		return $modelAgent->add( $data );
	}

	protected function listRecords( array $filters = [], array $orders = [] ): array
	{
		$orders				= $orders ?: ['logRecordId' => 'DESC'];
		$records			= $this->modelRecord->getAll( $filters, $orders, [10 ,0] );
		$listCategories		= [];
		$listClients		= [];
		$listHosts			= [];
		$listUserAgentId	= [];
		foreach( $records as $record ){
			$listCategories[$record->logCategoryId]		= $record->logCategoryId;
			$listClients[$record->logClientId]			= $record->logClientId;
			$listHosts[$record->logHostId]				= $record->logHostId;
			$listUserAgentId[$record->logUserAgentId]	= $record->logUserAgentId;
		}
		$modelCategory	= new Model_Log_Category( $this->env );
		$modelClient	= new Model_Log_Client( $this->env );
		$modelHost		= new Model_Log_Host( $this->env );
		$modelUserAgent	= new Model_Log_UserAgent( $this->env );
		if( $listCategories )
			foreach( $modelCategory->getAllByIndex( 'logCategoryId', array_keys( $listCategories ) ) as $category )
				$listCategories[$category->logCategoryId]	= $category;
		if( $listClients )
			foreach( $modelClient->getAllByIndex( 'logClientId', array_keys( $listClients ) ) as $client )
				$listClients[$client->logClientId]	= $client;
		if( $listHosts )
			foreach( $modelHost->getAllByIndex( 'logHostId', array_keys( $listHosts ) ) as $host )
				$listHosts[$host->logHostId]	= $host;
		$listUserAgents	= [];
		if( $listUserAgentId )
			foreach( $modelUserAgent->getAllByIndex( 'logUserAgentId', array_keys( $listUserAgentId ) ) as $userAgent )
				$listUserAgents[$userAgent->logUserAgentId]	= $userAgent;

		foreach( $records as $record ){
			$record->host		= (object) ['title' => NULL];
			$record->userAgent	= (object) ['title' => NULL];

			if( $record->logCategoryId )
				$record->category	= $listCategories[$record->logCategoryId];
			if( $record->logClientId )
				$record->client	= $listClients[$record->logClientId];
			if( $record->logHostId )
				$record->host	= $listHosts[$record->logHostId];
			if( $record->logUserAgentId )
				$record->userAgent	= $listUserAgents[$record->logUserAgentId];
		}
		return $records;
	}
}
