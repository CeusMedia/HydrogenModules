<?php
interface Logic_Import_Connector_Interface
{
	/**
	 *	@return		self
	 */
	public function connect(): self;

	/**
	 *	@return		void
	 */
	public function disconnect(): void;

	/**
	 *	@param		bool		$recursive
	 *	@return		array
	 */
	public function getFolders( bool $recursive = FALSE ): array;

	public function setConnection( object $connection );

	public function setConnectionId( int|string $connectionId );
}
