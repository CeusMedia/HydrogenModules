<?php

use CeusMedia\Mail\Mailbox;
use CeusMedia\Mail\Mailbox\Mail;
use CeusMedia\Mail\Mailbox\Search;

class Job_FormImport extends Job_Abstract
{
	protected $logicImport;
	protected $logicFill;
	protected $modelForm;
	protected $modelFill;
	protected $modelImportRule;
	protected $jsonMapper;
	protected $dataMapper;

	public function import( $verbose = FALSE )
	{
		$importRules	= $this->modelImportRule->getAll();
		foreach( $importRules as $importRule ){
//			print_m( $importRule );
			$connection	= $this->logicImport->getConnection( $importRule->importConnectionId );
//			print_m( $connection );
			$searchCriteria	= explode( PHP_EOL, $importRule->searchCriteria );
			$clock			= new Alg_Time_Clock();
			$messages		= $connection->find( $searchCriteria, [], [0, 1] );
			$verbose && $this->out( 'Found: '.count( $messages ).' mails' );
			$verbose && $this->out( 'Time needed: '.$clock->stop( 3, 0 ).' ms' );
		}

		foreach( $messages as $mailId => $message ){
			if( $verbose ){
				$this->out( str_pad( '--  #'.$mailId.'  ', '76', '-', STR_PAD_RIGHT ) );
				$this->out( 'Subject: '.$message->getSubject() );
				$this->out( 'Sender: '.$message->getSender()->get() );
				$this->displayMessageHeaders( $message );
				$this->displayMessageContent( $message );
			}
			if( $message->hasAttachments() ){
				foreach( $message->getAttachments() as $part ){
					$this->importMessageAttachment( $importRule, $part, $verbose );
				}
			}
		}
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->logicImport			= new Logic_Import( $this->env );
		$this->logicFill			= new Logic_Form_Fill( $this->env );
		$this->modelForm			= new Model_Form( $this->env );
		$this->modelFill			= new Model_Form_Fill( $this->env );
		$this->modelImportRule		= new Model_Form_Import_Rule( $this->env );
		$this->jsonParser			= new ADT_JSON_Parser;
		$this->dataMapper			= new Logic_Form_Transfer_DataMapper( $this->env );
	}

	protected function displayMessageContent( $message )
	{
		if( $message->hasText() ){
			$this->out( 'Text Part Content:' );
			$this->out( $message->getText()->getContent() );
			$this->out( '' );
		}
	}

	protected function displayMessageHeaders( $message )
	{
		$this->out( 'Headers:' );
		foreach( $message->getHeaders()->getFields() as $field ){
			if( $field->getName() === 'Authentication-Results' )
				continue;
			if( preg_match( '/^Received/', $field->getName() ) )
				continue;
			$this->out( '- '.$field->getName().': '.$field->getValue() );
		}
		$this->out();
	}

	protected function importCsvFile( $importRule, $filePath, bool $verbose = FALSE )
	{
		$ruleSet	= $this->jsonParser->parse( $importRule->rules, FALSE );
		$reader		= new FS_File_CSV_Reader( $filePath, FALSE, ';' );

		if( $verbose ){
			remark( 'Rules:' );
			print_m( $ruleSet );
		}
		foreach( $reader->toArray() as $importData ){
			if( !count( $importData ) )
				continue;
			if( $verbose ){
				remark( 'Import Data:' );
				print_m( $importData );
			}
			$fillId	= $this->importData( $importRule->formId, $ruleSet, $importData, $verbose );
		}
		return TRUE;
	}

	/**
	 *	@param		array		$importData			Data from connected import source
	 *	@param		boolean		$verbose			Show details in CLI output, default: no
	 *	@return		integer		Fill ID
	 */
	protected function importData( $formId, $ruleSet, array $importData, bool $verbose ): int
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

		$data		= array(
			'formId'		=> $formId,
			'status'		=> Model_Form_Fill::STATUS_CONFIRMED,
			'email'			=> $formData['email'] ?? '',
//				'data'			=> json_encode( $fillData, JSON_PRETTY_PRINT ),
			'data'			=> json_encode( $formData ),
			'referer'		=> '',//getEnv( 'HTTP_REFERER' ) ? strip_tags( getEnv( 'HTTP_REFERER' ) ) : '',
			'agent'			=> '',//strip_tags( getEnv( 'HTTP_USER_AGENT' ) ),
			'createdAt'		=> $formData['createdAt'] ?? time(),
			'modifiedAt'	=> time(),
		);
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

	protected function importMessageAttachment( $importRule, $part, bool $verbose = FALSE ): bool
	{
		$fileName	= $part->getFileName();
		$ext		= pathinfo( $fileName, PATHINFO_EXTENSION );
		if( $verbose ){
			$this->out( 'Attachment Part:' );
			$this->out( '- File Name: '.$fileName );
			$this->out( '- File Size: '.Alg_UnitFormater::formatBytes( $part->getFileSize() ) );
			$this->out( '- MIME Type: '.$part->getMimeType() );
			$this->out( str_repeat( '-', 76 ) );
		}
		if( strtolower( $ext ) !== 'csv' ){
			$this->out( '  - skipped: not a report: '.$fileName );
			return FALSE;
		}
		try{
			$tempFile	= tempnam( sys_get_temp_dir(), 'import' );
			file_put_contents( $tempFile, $part->getContent() );
			$result		= $this->importCsvFile( $importRule, $tempFile, $verbose );
			if( $verbose )
				$this->out( '- Import Result: '.$result );
			@unlink( $tempFile );
			return TRUE;
		}
		catch( Exception $e ){
			@unlink( $tempFile );
			$this->out( 'Error: '.$e->getMessage() );
			return FALSE;
		}
	}

	protected function translateFormDataToFillData( array $formData, $ruleSet ): array
	{
		$fillLabels			= (array) $ruleSet->label;
		$fillTypes			= (array) $ruleSet->type;
		$fillValueLabels	= (array) $ruleSet->valueLabel ?? [];

		$fillData	= [];
		foreach( $formData as $key => $value ){
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
