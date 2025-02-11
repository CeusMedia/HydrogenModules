<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\Response as HttpResponse;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class Controller_Manage_Form_Fill_Import extends Controller
{
	protected HttpRequest $request;

	protected HttpResponse $response;

	protected Logic_Import $logic;

	protected array $transferTargetMap	= [];

	protected array $allowedConnectorTypes	= [
		Model_Import_Connector::TYPE_PUSH_POST,
		Model_Import_Connector::TYPE_PUSH_PUT
	];

	/**
	 *	Constructor, disables automatic view instance.
	 *	@param		WebEnvironment		$env
	 *	@throws		ReflectionException
	 */
	public function __construct( WebEnvironment $env )
	{
		parent::__construct( $env, FALSE );
	}

	/**
	 *	@param		int|string		$importRuleId
	 *	@param		bool			$verbose
	 *	@param		bool			$dryMode
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( int|string $importRuleId, bool $verbose = FALSE, bool $dryMode = FALSE ): void
	{
		$this->response->setHeader( 'Content-type', 'text/plain' );
		$this->response->setStatus( 200 );
		$this->response->setBody( 'OK' );

		$this->checkImportRuleGiven( $importRuleId );

		$importRule	= $this->checkImportRuleExists( $importRuleId );

		$connector	= $this->logic->getConnectorFromConnectionId( $importRule->importConnectionId );

		$this->checkMimeTypeIsAllowed( $connector );
		$this->checkConnectorTypeIsAllowed( $connector );
		$this->checkRequestMethodIsAllowed( $connector );

		try{
			$results	= $this->getImportDataSets( $connector, $importRule );
			$errors		= $this->importDataSets( $results, $importRule, $verbose, $dryMode );

			if( 0 !== count( $errors ) ){
				$list	= PHP_EOL.'- '.implode( PHP_EOL.'- ', $errors );
				$this->response->setStatus( 500 );
				$this->response->setBody( 'There were '.count( $errors ).' errors: '.$list );
				$this->response->send();
			}
		}
		catch( Exception $e ){
			$this->response->setStatus( 500 );
			$this->response->setBody( 'Error: '.$e->getMessage() );
			$this->response->send();
		}
		$this->response->send();
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->response		= $this->env->getResponse();
		$this->logic		= new Logic_Import( $this->env );

	}

	/**
	 *	@param		int|string		$importRuleId
	 *	@return		void
	 */
	protected function checkImportRuleGiven( int|string $importRuleId ): void
	{
		if( 0 === (int) $importRuleId ){
			$this->response->setStatus( 400 );
			$this->response->setBody( 'No import rule ID given' );
			$this->response->send();
		}
	}

	/**
	 *	@param		int|string		$importRuleId
	 *	@return		Entity_Form_Import_Rule
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkImportRuleExists( int|string $importRuleId ): Entity_Form_Import_Rule
	{
		$model		= new Model_Form_Import_Rule( $this->env );
		/** @var ?Entity_Form_Import_Rule $importRule */
		$importRule	= $model->get( $importRuleId );

		if( NULL === $importRule ){
			$this->response->setStatus( 404 );
			$this->response->setBody( 'Access denied: Invalid ID given' );
			$this->response->send();
		}
		return $importRule;
	}

	/**
	 *	@param		object			$connector
	 *	@return		void
	 */
	protected function checkConnectorTypeIsAllowed( object $connector ): void
	{
		if( !in_array( (int) $connector->type, $this->allowedConnectorTypes, TRUE ) ){
			$this->response->setStatus( 401 );
			$this->response->setBody( 'Connection not allowed for push communication' );
			$this->response->send();
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

		if( !$matchingMethod ){
			$this->response->setStatus( 405 );
			$this->response->setBody( 'Invalid request method' );
			$this->response->send();
		}
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
			$this->response->setStatus( 406 );
			$this->response->setBody( 'Invalid content format: Supported MIME types are: '.join( ', ', $allowedMimeTypes ) );
			$this->response->send();
		}
	}

	/**
	 *	@param		Entity_Import_Connector		$connector
	 *	@param		Entity_Form_Import_Rule		$importRule
	 *	@return		array<Entity_Import_SourceItem>
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function getImportDataSets( Entity_Import_Connector $connector, Entity_Form_Import_Rule $importRule ): array
	{
//		$clock		= new Clock();
		$connection	= $this->logic->getConnectionInstanceFromId( $importRule->importConnectionId, $connector );
//		$connection->setOptions( $this->jsonParser->parse( $importRule->options ) );
		$searchCriteria	= explode( PHP_EOL, $importRule->searchCriteria );
		return $connection->find( $searchCriteria, [], [0, 10] );
	}

	/**
	 *	@param		Entity_Import_SourceItem[]	$results
	 *	@param		Entity_Form_Import_Rule		$importRule
	 *	@param		bool						$verbose
	 *	@param		bool						$dryMode
	 *	@return		array
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function importDataSets( array $results, Entity_Form_Import_Rule $importRule, bool $verbose, bool $dryMode ): array
	{
		$errors	= [];
		foreach( $results as $result ){
			foreach( $result->data as $dataSet ){
				foreach( $dataSet as $data ){
					try {
						$this->logic->importData( $importRule, $data, $verbose, $dryMode );
					}
					catch( Exception $e ){
						$errors[]	= $e->getMessage();
						$this->env->getLog()->logException( $e );
					}
				}
			}
		}
		return $errors;
	}
}