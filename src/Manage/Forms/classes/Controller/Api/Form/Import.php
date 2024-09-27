<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Controller\Api as Controller;

class Controller_Api_Form_Import extends Controller
{
	protected Logic_Import $logic;
	protected Model_Form_Import_Rule $modelRule;
	protected array $transferTargetMap	= [];
	protected array $allowedConnectorTypes	= [
		Model_Import_Connector::TYPE_PUSH_POST,
		Model_Import_Connector::TYPE_PUSH_PUT
	];

	/**
	 *	@param		int|string		$importRuleId
	 *	@param		bool			$verbose
	 *	@param		bool			$dryMode
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( int|string $importRuleId = 0, bool $verbose = FALSE, bool $dryMode = FALSE ): void
	{
		$this->response->setHeader( 'Content-type', 'text/plain' );

		try{
			$importRule	= $this->tryToGetImportRule( $importRuleId );
			$connector	= $this->tryToGetConnector( $importRule );
			$results	= $this->getImportDataSets( $connector, $importRule );
			$this->importDataSets( $results, $importRule, $verbose, $dryMode );
			$this->respondData( 'OK' );
		}
		catch( Exception $e ){
			$this->respondError( 0, 'Error: '.$e->getMessage(), 500 );
		}
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->logic		= new Logic_Import( $this->env );
		$this->modelRule	= new Model_Form_Import_Rule( $this->env );
	}

	protected function checkAuthentication( object $connection ): void
	{
		if( Model_Import_Connection::AUTH_TYPE_KEY === (int) $connection->authType ){
			$header	= $this->request->getHeader( 'X-API-Key', FALSE );
			if( NULL === $header )
				throw new RuntimeException( 'Access denied: Missing API key' );
			if( $header->getValue() !== $connection->authKey )
				throw new RuntimeException( 'Access denied: Invalid API key' );
		}

	}

	/**
	 *	@param		object		$connector
	 *	@return		void
	 */
	protected function checkRequestMethodIsAllowed( object $connector ): void
	{
		$requestMethod	= $this->request->getMethod();
		$matchingMethod	= match( (int) $connector->type ){
			Model_Import_Connector::TYPE_PUSH_POST	=> $requestMethod->isPost(),
			Model_Import_Connector::TYPE_PUSH_PUT	=> $requestMethod->isPut(),
			default									=> FALSE,
		};

		if( !$matchingMethod )
			$this->respondError( 405, 'Invalid request method' );
	}

	/**
	 *	@param		object		$connector
	 *	@return		void
	 */
	protected function checkMimeTypeIsAllowed( object $connector ): void
	{
		$setMimeTypes	= $connector->mimeTypes ?? '';
		if( '' === $setMimeTypes )
			return;
		$allowedMimeTypes	= explode(',', $setMimeTypes );
		$requestMimeType	= $this->request->getHeader( 'Content-Type', FALSE )?->getValue();
		if( !in_array( $requestMimeType, $allowedMimeTypes, TRUE ) ){
			$this->respondError( 406, 'Invalid content format: Supported MIME types are: '.join( ', ', $allowedMimeTypes ) );
		}
	}

	/**
	 *	@param		object		$connector
	 *	@param		object		$importRule
	 *	@return		array
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function getImportDataSets( object $connector, object $importRule ): array
	{
//		$clock		= new Clock();
		$connection	= $this->logic->getConnectionInstanceFromId( $importRule->importConnectionId, $connector );
//		$connection->setOptions( $this->jsonParser->parse( $importRule->options ) );
		$searchCriteria	= explode( PHP_EOL, $importRule->searchCriteria );
		return $connection->find( $searchCriteria, [], [0, 10] );
	}

	/**
	 *	@param		array		$results
	 *	@param		object		$importRule
	 *	@param		bool		$verbose
	 *	@param		bool		$dryMode
	 *	@return		int			Number of imported data sets
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function importDataSets( array $results, object $importRule, bool $verbose, bool $dryMode ): int
	{
		$valid		= [];
		$errors		= [];
		$counter	= 0;
		foreach( $results as $result ){
			foreach( $result->data as $dataSet ){
				foreach( $dataSet as $data ){
					try {
						$this->logic->importData( $importRule, $data, $verbose, TRUE );
						$valid[]	= $data;
						$counter++;
					}
					catch( Exception $e ){
						$errors[]	= $e->getMessage();
						$this->env->getLog()->logException( $e );
					}
				}
			}
		}
		if( 0 !== count( $errors ) ){
			$list	= PHP_EOL.'- '.implode( PHP_EOL.'- ', $errors );
			$this->respondError( 500, 'There were '.count( $errors ).' errors: '.$list );
		}
		foreach( $valid as $data )
			$this->logic->importData( $importRule, $data, $verbose, $dryMode );
		return $counter;
	}

	/**
	 *	@return		?object
	 */
	protected function tryToFindImportRuleByConnectionApiKey(): ?object
	{
		$apiKey	= $this->request->getHeader( 'X-API-Key', FALSE ) ?? '';
		if( '' !== $apiKey ){
			$connection	= $this->logic->getConnectionFromApiKey( $apiKey );
			if( NULL !== $connection )
				return $this->modelRule->getByIndex( 'importConnectionId', $connection->importConnectionId );
		}
		return NULL;
	}

	/**
	 *	@param		object		$importRule
	 *	@return		object
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function tryToGetConnector( object $importRule ): object
	{
		$connector	= $this->logic->getConnectorFromConnectionId( $importRule->importConnectionId );
		if( !in_array( (int) $connector->type, $this->allowedConnectorTypes, TRUE ) )
			$this->respondError( 401, 'Connection not allowed for push communication' );

		$this->checkMimeTypeIsAllowed( $connector );
		$this->checkRequestMethodIsAllowed( $connector );
		return $connector;
	}

	/**
	 *	@param		int|string		$importRuleId
	 *	@return		object
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function tryToGetImportRule( int|string $importRuleId ): object
	{
		$importRule	= $this->tryToFindImportRuleByConnectionApiKey();
		if( NULL === $importRule ){
			if( 0 === (int) $importRuleId )
				$this->respondError( 400, 'No import rule ID given' );

			/** @var ?object $importRule */
			$importRule	= $this->modelRule->get( $importRuleId );
			if( NULL === $importRule )
				$this->respondError( 404, 'Access denied: Invalid ID given' );
			$connection	= $this->logic->getConnection( $importRule->importConnectionId );
			$this->checkAuthentication( $connection );
		}
		return $importRule;
	}
}
