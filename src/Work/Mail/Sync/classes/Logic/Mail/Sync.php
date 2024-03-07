<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_Mail_Sync extends Logic
{
	protected Model_Mail_Sync_Host $modelHost;
	protected Model_Mail_Sync_Run $modelRun;
	protected Model_Mail_Sync $modelSync;

	public function addSync( array $data ): string
	{
		$data['createdAt']	= time();
		$data['modifiedAt']	= time();
		return $this->modelSync->add( $data );
	}

	public function addSyncHost( array $data ): string
	{
		$data['createdAt']	= time();
		$data['modifiedAt']	= time();
		return $this->modelHost->add( $data );
	}

	public function addSyncRun( int|string $syncId ): string
	{
		$data	= array(
			'mailSyncId'	=> $syncId,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		);
		return $this->modelRun->add( $data );
	}

	public function editSync( int|string $id, array $data ): int
	{
		$data['modifiedAt']	= time();
		return $this->modelSync->edit( $id, $data );
	}

	public function getSync( int|string $id ): object
	{
		return $this->modelSync->get( $id );
	}

	public function editSyncHost( int|string $id, array $data ): int
	{
		$data['modifiedAt']	= time();
		return $this->modelHost->edit( $id, $data );
	}

	public function editSyncRun( int|string $id, array $data ): int
	{
		$data['modifiedAt']	= time();
		return $this->modelRun->edit( $id, $data );
	}

	public function getSyncHost( int|string $id ): object
	{
		return $this->modelHost->get( $id );
	}

	public function getSyncRun( int|string $id ): object
	{
		return $this->modelRun->get( $id );
	}

	public function getSyncs( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelSync->getAll( $conditions, $orders, $limits );
	}

	public function getSyncHosts( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelHost->getAll( $conditions, $orders, $limits );
	}

	public function getSyncRuns( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelRun->getAll( $conditions, $orders, $limits );
	}

	protected function __onInit(): void
	{
		$this->modelSync	= new Model_Mail_Sync( $this->env );
		$this->modelHost	= new Model_Mail_Sync_Host( $this->env );
		$this->modelRun		= new Model_Mail_Sync_Run( $this->env );
	}
}
