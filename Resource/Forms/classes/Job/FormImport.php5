<?php

use CeusMedia\Mail\Mailbox;
use CeusMedia\Mail\Mailbox\Mail;
use CeusMedia\Mail\Mailbox\Search;

class Job_FormImport extends Job_Abstract
{
	protected $logicImport;
	protected $logicFill;
	protected $modelConnection;
	protected $modelConnector;
	protected $modelForm;
	protected $modelFill;
	protected $modelImportRule;
	protected $jsonMapper;
	protected $dataMapper;

	public function import( $arguments = array() )
	{
		$verbose		= in_array( 'verbose', $arguments ) || $this->parameters->get( 'verbose' );
		$dryMode		= in_array( 'dry', $arguments ) || $this->parameters->get( 'dry' );
		$errors			= [];
		$importRules	= $this->getActiveFormImportRules();
		foreach( $importRules as $importRule ){
			$connection			= $this->modelConnection->get( $importRule->importConnectionId );
			$connector			= $this->modelConnector->get( $connection->importConnectorId );
			$connectionInstance	= $this->logicImport->getConnectionInstanceFromId( $importRule->importConnectionId );
			$connectionInstance->setOptions( $this->jsonParser->parse( $importRule->options, FALSE ) );
			$searchCriteria		= explode( PHP_EOL, $importRule->searchCriteria );
			$clock				= new Alg_Time_Clock();
			$results			= $connectionInstance->find( $searchCriteria, [], [0, 1] );

			if( $verbose ){
				$this->out( 'Rule: '.$importRule->title );
				$this->out( 'Form: '.$importRule->form->title );
				$this->out( 'Connection: '.$connection->title );
				$this->out( 'Connector: '.$connector->title );
				$this->out( 'Data Sources: '.count( $results ).' found' );
				$this->out( 'Time needed: '.$clock->stop( 3, 0 ).' ms' );
			}
			$ruleSet	= $this->jsonParser->parse( $importRule->rules, FALSE );
			foreach( $results as $result ){
				foreach( $result->data as $dataSet ){
					foreach( $dataSet as $data ){
						try{
							$fillId	= $this->importData( $importRule, $ruleSet, $data, $verbose, $dryMode );
						}
						catch( Exception $e ){
							$errors[]	= $e->getMessage();
							$this->env->getLog()->logException( $e );
						}
					}
				}
				if( !$dryMode ){
					if( $importRule->moveTo )
						$connectionInstance->moveTo( $result->source->id, $importRule->moveTo );
				//	if( $importRule->renameTo )
				//		$connectionInstance->renameTo( $result->source->id, $importRule->renameTo );
				}
			}
		}
		return $errors;
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->logicImport			= new Logic_Import( $this->env );
		$this->logicFill			= new Logic_Form_Fill( $this->env );
		$this->modelConnection		= new Model_Import_Connection( $this->env );
		$this->modelConnector		= new Model_Import_Connector( $this->env );
		$this->modelForm			= new Model_Form( $this->env );
		$this->modelFill			= new Model_Form_Fill( $this->env );
		$this->modelImportRule		= new Model_Form_Import_Rule( $this->env );
		$this->jsonParser			= new ADT_JSON_Parser;
		$this->dataMapper			= new Logic_Form_Transfer_DataMapper( $this->env );
	}

	protected function getActiveFormImportRules(): array
	{
		$rules		= array();
		$forms		= $this->modelForm->getAllByIndex( 'status', Model_Form::STATUS_ACTIVATED );
		if( count( $forms ) !== 0 ){
			$formMap	= array();
			foreach( $forms as $form )
				$formMap[$form->formId]	= $form;
			$conditions		= array(
				'formId'	=> array_keys( $formMap ),
				'status'	=> array(
					Model_Form_Import_Rule::STATUS_TEST,
					Model_Form_Import_Rule::STATUS_ACTIVE,
				),
			);
			$orders		= array(
				'importConnectionId'	=> 'ASC',
				'formId'				=> 'ASC',
			);
			$limits		= array();
			$rules		= $this->modelImportRule->getAll( $conditions, $orders, $limits );
			foreach( $rules as $rule )
				$rule->form = $formMap[$rule->formId];
		}
		return $rules;
	}

	/**
	 *	...
	 *	@access		protected
	 *	@param		object		$importRule			Data object of form input rule
	 *	@param		object		$ruleSet			Data object of rules for data mapper
	 *	@param		array		$importData			Data from connected import source
	 *	@param		boolean		$verbose			Show details in CLI output, default: no
	 *	@param		boolean		$dryMode			Flag: do not change anything, default: no
	 *	@return		integer		Fill ID
	 */
	protected function importData( $importRule, $ruleSet, array $importData, bool $verbose, bool $dryMode ): int
	{
		if( !count( $importData ) )
			throw new Exception( 'No import data given.' );

		if( $verbose ){
			remark( 'Import Data:' );
			print_m( $importData );
		}
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
		$createdAt	= $formData['createdAt'] ?? time();

		$data		= array(
			'formId'		=> $importRule->formId,
			'status'		=> Model_Form_Fill::STATUS_CONFIRMED,
			'email'			=> $formData['email'] ?? '',
			'data'			=> json_encode( $fillData ),
			'referer'		=> '',//getEnv( 'HTTP_REFERER' ) ? strip_tags( getEnv( 'HTTP_REFERER' ) ) : '',
			'agent'			=> '',//strip_tags( getEnv( 'HTTP_USER_AGENT' ) ),
			'createdAt'		=> $formData['createdAt'] ?? time(),
			'modifiedAt'	=> time(),
		);
		if( $dryMode || $importRule->status == Model_Form_Import_Rule::STATUS_TEST )
			return 0;

		$fillId		= $this->modelFill->add( $data, FALSE );
		if( $verbose ){
			remark( 'Fill Entry Data:' );
			print_m( $data );
			remark( 'Fill ID: '.$fillId );
		}
//		$this->logicFill->sendCustomerResultMail( $fillId );
//		$this->logicFill->sendManagerResultMails( $fillId );
		$this->logicFill->applyTransfers( $fillId );
		return $fillId;
	}

	protected function translateFormDataToFillData( array $formData, $ruleSet ): array
	{
		$fillLabels			= (array) $ruleSet->label;
		$fillTypes			= (array) $ruleSet->type;
		$fillValueLabels	= (array) $ruleSet->valueLabel ?? [];

		//  get value labels for base and topic from database (school_bases, school_courses)
		foreach( $formData as $key => $value ){
			if( in_array( $key, ['base', 'topic'] ) ){
				if( $key === 'base' )
					$model	= new Model_School_Base( $this->env );
				else if( $key === 'topic' )
					$model	= new Model_School_Course( $this->env );
				$entry	= $model->getByIndex( 'identifier', $value );
				if( $entry )
					$fillValueLabels[$key.'-'.$value]	= $entry->title;
			}
		}

		$fillData	= [];
		foreach( $formData as $key => $value ){
			if( in_array( $key, ['createdAt'], TRUE ) )
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

	protected function translateImportDataToFormData( array $importData, $ruleSet ): array
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
