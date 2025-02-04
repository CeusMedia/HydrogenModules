<?php
interface Logic_Import_Connector_Interface
{
	/**
	 *	@return		static
	 */
	public function connect(): static;

	/**
	 *	@return		void
	 */
	public function disconnect(): void;

	/**
	 *	@param		bool		$recursive
	 *	@return		array
	 */
	public function getFolders( bool $recursive = FALSE ): array;

	public function setConnection( Entity_Import_Connection $connection );

	public function setConnectionId( int|string $connectionId );
}
