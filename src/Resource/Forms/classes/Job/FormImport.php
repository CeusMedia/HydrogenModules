<?php

use CeusMedia\Common\ADT\JSON\Parser as JsonParser;
use CeusMedia\Common\Alg\Time\Clock;

class Job_FormImport extends Job_Abstract
{
	protected JsonParser $jsonParser;
	protected Logic_Import $logicImport;
	protected Logic_Form_Transfer_DataMapper $dataMapper;
	protected Model_Import_Connection $modelConnection;
	protected Model_Import_Connector $modelConnector;
	protected Model_Form $modelForm;
	protected Model_Form_Fill $modelFill;
	protected Model_Form_Import_Rule $modelImportRule;

	/**
	 *	@param		array<string>		$arguments
	 *	@return		array<string>
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function import( array $arguments = [] ): array
	{
//		Encoding::$decodeStrategy = Encoding::DECODE_STRATEGY_ICONV_TOLERANT;
		$verbose		= in_array( 'verbose', $arguments ) || $this->parameters->get( 'verbose' );
		$dryMode		= in_array( 'dry', $arguments ) || $this->parameters->get( 'dry' );
		$errors			= [];
		$importRules	= $this->getActiveFormImportRules();
		foreach( $importRules as $importRule ){
			$connection			= $this->modelConnection->get( $importRule->importConnectionId );
			$connector			= $this->modelConnector->get( $connection->importConnectorId );
			$connectionInstance	= $this->logicImport->getConnectionInstanceFromId( $importRule->importConnectionId );
			$connectionInstance->setOptions( $this->jsonParser->parse( $importRule->options ) );
			$searchCriteria		= explode( PHP_EOL, $importRule->searchCriteria );
			$clock				= new Clock();
			$results			= $connectionInstance->find( $searchCriteria, [], [0, 10] );

			if( $verbose ){
				$this->out( 'Rule: '.$importRule->title );
				$this->out( 'Form: '.$importRule->form->title );
				$this->out( 'Connection: '.$connection->title );
				$this->out( 'Connector: '.$connector->title );
				$this->out( 'Data Sources: '.count( $results ).' found' );
				$this->out( 'Time needed: '.$clock->stop( 3, 0 ).' ms' );
			}
			foreach( $results as $result ){
				if( !$dryMode ){
					if( $importRule->moveTo )
						$connectionInstance->moveTo( $result->source->id, $importRule->moveTo );
				//	if( $importRule->renameTo )
				//		$connectionInstance->renameTo( $result->source->id, $importRule->renameTo );
				}
				foreach( $result->data as $dataSet ){
					foreach( $dataSet as $data ){
						try{
							$this->logicImport->importData( $importRule, $data, $verbose, $dryMode );
						}
						catch( Exception $e ){
							$errors[]	= $e->getMessage();
							$this->env->getLog()->logException( $e );
						}
					}
				}
			}
		}
		return $errors;
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function test(): void
	{
		$importRules	= $this->getActiveFormImportRules();
		foreach( $importRules as $importRule ){

//			$connection			= $this->modelConnection->get( $importRule->importConnectionId );
//			$connector			= $this->modelConnector->get( $connection->importConnectorId );

			$connection		= $this->logicImport->getConnectionInstanceFromId( $importRule->importConnectionId );
			$connection->setOptions( $this->jsonParser->parse( $importRule->options ) );

			$searchCriteria		= explode( PHP_EOL, $importRule->searchCriteria );
//			$clock				= new Clock();
			$results			= $connection->find( $searchCriteria, [], [0, 10] );
			echo "The current read timeout is " . imap_timeout(IMAP_READTIMEOUT) . "\n";
//			$connectionInstance->disconnect();
			break;
		}
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logicImport			= new Logic_Import( $this->env );
		$this->modelConnection		= new Model_Import_Connection( $this->env );
		$this->modelConnector		= new Model_Import_Connector( $this->env );
		$this->modelForm			= new Model_Form( $this->env );
		$this->modelImportRule		= new Model_Form_Import_Rule( $this->env );
		$this->jsonParser			= new JsonParser;
	}

	/**
	 * @return array<Entity_Form_Import_Rule>
	 * @todo filter out controller based rules, perhaps introduce new type: async (job based) and sync (HTTP based)
	 */
	protected function getActiveFormImportRules(): array
	{
		/** @var Entity_Form[] $forms */
		$forms		= $this->modelForm->getAllByIndex( 'status', Model_Form::STATUS_ACTIVATED );
		if( [] === $forms )
			return [];

		$formMap	= [];
		foreach( $forms as $form )
			$formMap[$form->formId]	= $form;
		$conditions		= [
			'formId'	=> array_keys( $formMap ),
			'status'	=> [
				Model_Form_Import_Rule::STATUS_TEST,
				Model_Form_Import_Rule::STATUS_ACTIVE,
			],
		];
		$orders		= [
			'importConnectionId'	=> 'ASC',
			'formId'				=> 'ASC',
		];
		$limits		= [];
		/** @var Entity_Form_Import_Rule[] $rules */
		$rules		= $this->modelImportRule->getAll( $conditions, $orders, $limits );
		foreach( $rules as $rule )
			$rule->form = $formMap[$rule->formId];
		return $rules;
	}
}
