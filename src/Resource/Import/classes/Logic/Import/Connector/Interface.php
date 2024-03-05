<?php
interface Logic_Import_Connector_Interface
{
	public function connect();

	public function disconnect();

	public function setConnection( object $connection );

	public function setConnectionId( int|string $connectionId );

	public function getFolders( bool $recursive = FALSE ): array;
}
