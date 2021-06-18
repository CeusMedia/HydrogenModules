<?php
interface Logic_Import_Connector_Interface
{
	public function connect();

	public function disconnect();

	public function setConnection( $connection );

	public function setConnectionId( $connectionId );
}
