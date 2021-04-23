<?php
class Logic_Import_Connector_Abstract extends CMF_Hydrogen_Logic
{
	protected $modelConnection;

	public function getConnection()
	{
		return $this->connection;
	}

	public function setConnection( $connection  )
	{
		$this->connection	= $connection;
	}

	public function setConnectionId( $connectionId  )
	{
		$this->connection	= $this->modelConnection->get( $connectionId );
	}

	protected function __onInit()
	{
		$this->modelConnection	= new Model_Import_Connection( $this->env );
	}
}
