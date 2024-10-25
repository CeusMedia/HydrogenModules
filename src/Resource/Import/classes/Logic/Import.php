<?php

use CeusMedia\Common\ADT\JSON\Parser as JsonParser;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Exception\NotEnabled as NotEnabledException;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Import extends Logic
{
	/** @var Logic_Form_Fill $logicFill */
	protected Logic_Form_Fill $logicFill;

	/** @var Model_Import_Connection $modelConnection  */
	protected Model_Import_Connection $modelConnection;

	/** @var Model_Import_Connector $modelConnector  */
	protected Model_Import_Connector $modelConnector;

	/** @var Model_Form_Fill $modeFill */
	protected Model_Form_Fill $modelFill;

	/** @var Logic_Import_Connector_Interface[] $connections  */
	protected array $connections		= [];

	protected JsonParser $jsonParser;

	protected Logic_Form_Transfer_DataMapper $dataMapper;

	/**
	 *	@param		int|string		$connectionId
	 *	@return		?object
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getConnection( int|string $connectionId ): ?object
	{
		return $this->modelConnection->get( $connectionId );
	}

	/**
	 *	Tries to find exactly one connection by API key.
	 *	Will only look at enabled connections.
	 *	@param		string		$apiKey
	 *	@return		?object
	 */
	public function getConnectionFromApiKey( string $apiKey ): ?object
	{
		$connections	= $this->modelConnection->getAllByIndices( [
			'authType'	=> Model_Import_Connection::AUTH_TYPE_KEY,
			'status'	=> Model_Import_Connection::STATUS_ENABLED,
			'authKey'	=> $apiKey,
		] );
		return ( 1 === count( $connections ) ) ? current( $connections ) : NULL;

	}

	/**
	 *	@param		int|string		$connectionId
	 *	@param		?object			$connector			Connector data object, optional, if already available
	 *	@return		Logic_Import_Connector_Interface
	 *	@throws		RangeException			if connection ID is invalid
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getConnectionInstanceFromId( int|string $connectionId, ?object $connector = NULL ): Logic_Import_Connector_Interface
	{
		if( !isset( $this->connections[$connectionId] ) ){
			/** @var object $connection */
			$connection	= $this->modelConnection->get( $connectionId );
			if( !$connection )
				throw new RangeException( 'Invalid connection ID' );
			$connector	= $connector ?? $this->modelConnector->get( $connection->importConnectorId  );
			if( (int) $connector->status !== Model_Import_Connector::STATUS_ENABLED )
				throw new NotEnabledException( 'Connector "'.$connector->title.'" is not enabled' );

			/** @var Logic_Import_Connector_Interface $instance */
			$instance	= ObjectFactory::createObject( $connector->className, [$this->env] );
			$instance->setConnection( $connection );
			$this->connections[$connectionId]	= $instance->connect();
		}
		return $this->connections[$connectionId];
	}

	/**
	 *	@param		int|string		$connectionId
	 *	@return		?object
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getConnectorFromConnectionId( int|string $connectionId ): ?object
	{
		$connection	= $this->modelConnection->get( $connectionId );
		if( !$connection )
			throw new RangeException( 'Invalid connection ID' );
		return $this->modelConnector->get( $connection->importConnectorId  );
	}

	/**
	 *	...
	 *	@param		object		$importRule			Data object of form input rule
	 *	@param		array		$importData			Data from connected import source
	 *	@param		boolean		$verbose			Show details in CLI output, default: no
	 *	@param		boolean		$dryMode			Flag: do not change anything, default: no
	 *	@return		integer		Fill ID
	 *	@throws		RuntimeException				if not data given
	 *	@throws		RuntimeException				if parsing JSON of rule failed
	 *	@throws		ReflectionException
	 *	@throws		JsonException					if encoding sent data as JSON failed
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function importData( object $importRule, array $importData, bool $verbose, bool $dryMode ): int
	{
		if( !count( $importData ) )
			throw new RuntimeException( 'No import data given.' );

		if( $verbose ){
			remark( 'Import Data:' );
			print_m( $importData );
		}
		$ruleSet	= $this->jsonParser->parse( $importRule->rules );
		$formData	= $this->translateImportDataToFormData( $importData, $ruleSet );
		if( $verbose ){
			remark( 'Form Data:' );
			print_m( $formData );
		}
		$fillData	= $this->translateFormDataToFillData( $formData, $ruleSet );
		if( $verbose ){
			remark( 'Fill Data:' );
			print_m( $fillData );
		}

		$agent  = '';
		if( '' !== ( $formData['agent'] ?? '' ) )
			$agent  = $formData['agent'];
		else if( '' !== ( getEnv( 'HTTP_USER_AGENT' ) ?? '' ) )
			$agent  = strip_tags( getEnv( 'HTTP_USER_AGENT' ) );

		$data		= [
			'formId'		=> $importRule->formId,
			'status'		=> Model_Form_Fill::STATUS_CONFIRMED,
			'email'			=> $formData['email'] ?? '',
			'data'			=> json_encode( $fillData, JSON_THROW_ON_ERROR ),
			'referer'		=> '',//getEnv( 'HTTP_REFERER' ) ? strip_tags( getEnv( 'HTTP_REFERER' ) ) : '',
			'agent'			=> $agent,
			'createdAt'		=> $formData['createdAt'] ?? time(),
			'modifiedAt'	=> time(),
		];
		if( $dryMode || $importRule->status == Model_Form_Import_Rule::STATUS_TEST )
			return 0;

		$fillId		= $this->modelFill->add( $data, FALSE );
		if( $verbose ){
			remark( 'Fill Entry Data:' );
			print_m( $data );
			remark( 'Fill ID: '.$fillId );
		}
		$this->logicFill->sendCustomerResultMail( $fillId );
		$this->logicFill->sendManagerResultMails( $fillId );
		$this->logicFill->applyTransfers( $fillId );
		return $fillId;
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->logicFill		= new Logic_Form_Fill( $this->env );
		$this->modelConnection	= new Model_Import_Connection( $this->env );
		$this->modelConnector	= new Model_Import_Connector( $this->env );
		$this->modelFill		= new Model_Form_Fill( $this->env );
		$this->jsonParser		= new JsonParser;
		$this->dataMapper		= new Logic_Form_Transfer_DataMapper( $this->env );
	}

	/**
	 *	@param		array		$formData
	 *	@param		object		$ruleSet
	 *	@return		array<string,array>
	 *	@throws		ReflectionException
	 */
	protected function translateFormDataToFillData( array $formData, object $ruleSet ): array
	{
		$payload	= [
			'formData'			=> $formData,
			'fillLabels'		=> (array) $ruleSet->label,
			'fillTypes'			=> (array) $ruleSet->type,
			'fillValueLabels'	=> (array) $ruleSet->valueLabel ?? [],
		];
		$this->env->getCaptain()->callHook( 'Import', 'translateFormFillData', $this, $payload );

		/** @var array<string,string> $fillLabels */
		/** @var array<string,string> $fillTypes */
		/** @var array<string,string> $fillValueLabels */
		extract( $payload );

		$skip		= ['createdAt'];
		$fillData	= [];
		foreach( $formData as $key => $value ){
			if( in_array( $key, $skip, TRUE ) )
				continue;
			$value	= strip_tags( $value );
			$fillData[$key]	= [
				'id'			=> 'import_'.$key,
				'type'			=> $fillTypes[$key] ?? 'text',
				'name'			=> $key,
				'label'			=> $fillLabels[$key] ?? $key,
				'value'			=> $value,
				'valueLabel'    => $fillValueLabels[$key.'-'.$value] ?? $value,
			];
		}
		return $fillData;
	}

	/**
	 *	@param		array		$importData
	 *	@param		object		$ruleSet
	 *	@return		array<string,string>
	 */
	protected function translateImportDataToFormData( array $importData, object $ruleSet ): array
	{
		$formData	= $this->dataMapper->applyRulesToFormData( $importData, $ruleSet );
		$formData['createdAt']	= time();
		if( isset( $formData['creationDateTime'] ) ){
			$formData['createdAt']	= strtotime( $formData['creationDateTime'] );
			unset( $formData['creationDateTime'] );
		}
		return $formData;
	}
}
