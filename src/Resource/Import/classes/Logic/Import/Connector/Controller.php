<?php

class Logic_Import_Connector_Controller extends Logic_Import_Connector_Abstract implements Logic_Import_Connector_Interface
{
	/**
	 *	@return		self
	 */
	public function connect(): self
	{
		return $this;
	}

	/**
	 *	@return		void
	 */
	public function disconnect(): void
	{
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limit
	 *	@return		array
	 *	@throws		JsonException
	 */
	public function find( array $conditions, array $orders = [], array $limit = [] ): array
	{
		if( NULL === $this->connection )
			throw new RuntimeException( 'No connection set' );

		$request	= $this->env->getRequest();

//		if( '' !== ( $this->connection->hostPath ?? '' ) && $request->getUrl() !== $this->connection->hostPath )
//			throw new RuntimeException( 'Access denied: Path mismatch' );

		if( Model_Import_Connection::AUTH_TYPE_KEY === (int) $this->connection->authType ){
			$header	= $request->getHeader( 'X-API-Key', FALSE );
			if( NULL === $header )
				throw new RuntimeException( 'Access denied: Missing API key' );
			if( $header->getValue() !== $this->connection->authKey )
				throw new RuntimeException( 'Access denied: Invalid API key' );
		}

		$rawData	= $request->getRawPostData();
		if( '' === trim( $rawData ) )
			throw new RuntimeException( 'Missing JSON in POST body' );

		if( !str_starts_with( $rawData, '[' ) )
			$rawData	= '['.$rawData.']';
		if( !str_starts_with( $rawData, '[{' ) )
			throw new RuntimeException( 'JSON invalid, must start with { or [{' );

		$data	= json_decode( $rawData, TRUE, 512, JSON_THROW_ON_ERROR );
		return [(object)['data' => [$data]]];
	}

	/**
	 *	@param		bool		$recursive
	 *	@return		array
	 */
	public function getFolders( bool $recursive = FALSE ): array
	{
		return [];
	}
}