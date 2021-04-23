<?php

use CeusMedia\Mail\Mailbox;
use CeusMedia\Mail\Mailbox\Mail;
use CeusMedia\Mail\Mailbox\Search;

class Job_FormImport extends Job_Abstract
{
//	protected $logic;
//	protected $modelImportSource;
	protected $modelImportRule;
	protected $modelFill;
	protected $modelForm;

	public function __onInit(){
		$this->logicImport			= new Logic_Import( $this->env );
//		$this->modelImportSource	= new Model_Form_Import_Source( $this->env );
		$this->modelImportRule		= new Model_Form_Import_Rule( $this->env );
		$this->modelFill			= new Model_Form_Fill( $this->env );
		$this->modelForm			= new Model_Form( $this->env );
		$this->path					= 'm/';
	}

	public function import(){
		$verbose	= TRUE;

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
			$this->out( str_pad( '--  #'.$mailId.'  ', '76', '-', STR_PAD_RIGHT ) );
			$this->out( 'Subject: '.$message->getSubject() );
			$this->out( 'Sender: '.$message->getSender()->get() );
			$this->displayMessageHeaders( $message );
			$this->displayMessageContent( $message );
			if( $message->hasAttachments() ){
				foreach( $message->getAttachments() as $part ){
					$this->importMessageAttachment( $importRule, $part );
				}
			}
		}
	}

	//  --  PROTECTED  --  //

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

	protected function importCsvFile( $importRule, $filePath )
	{
		$parser		= new ADT_JSON_Parser;
		$ruleSet	= $parser->parse( $importRule->rules, FALSE );
		$fillLabels	= (array) $ruleSet->type;
		$fillTypes	= (array) $ruleSet->label;

remark( 'Rules:' );
print_m( $ruleSet );
		$reader	= new FS_File_CSV_Reader( $filePath, FALSE, ';' );
		foreach( $reader->toArray() as $importData ){

remark( 'Import Data:' );
print_m( $importData );
			if( !count( $importData ) )
				throw new Exception( 'No import data given.' );

			$mapper	= new Logic_Form_Transfer_DataMapper( $this->env );
			$formData	= $mapper->applyRulesToFormData( $importData, $ruleSet );
			$createdAt	= time();
			if( isset( $formData['creationDateTime'] ) ){
				$createdAt	= strtotime( $formData['creationDateTime'] );
				unset( $formData['creationDateTime'] );
			}
remark( 'Form Data:' );
print_m( $formData );
			$fillData	= [];
			foreach( $formData as $key => $value ){
				$fillData[$key]	= [
					'id'			=> 'import_'.$key,
					'type'			=> $fillTypes[$key] ?? 'text',
					'name'			=> $key,
					'label'			=> $fillLabels[$key] ?? $key,
					'value'			=> strip_tags( $value ),
					'valueLabel'	=> '',
				];
			}
remark( 'Fill Data:' );
print_m( $fillData );
			$data		= array(
				'formId'		=> $importRule->formId,
				'status'		=> Model_Form_Fill::STATUS_CONFIRMED,
				'email'			=> $formData['email'] ?? '',
				'data'			=> json_encode( $fillData, JSON_PRETTY_PRINT ),
//				'data'			=> json_encode( $formData ),
				'referer'		=> '',//getEnv( 'HTTP_REFERER' ) ? strip_tags( getEnv( 'HTTP_REFERER' ) ) : '',
				'agent'			=> '',//strip_tags( getEnv( 'HTTP_USER_AGENT' ) ),
				'createdAt'		=> $formData['createdAt'] ?? time(),
				'modifiedAt'	=> time(),
			);
remark( 'Fill Entry Data:' );
print_m( $data );
die;
			$fillId		= $this->modelFill->add( $data, FALSE );
remark( 'Fill ID: '.$fillId );
die;
//			$this->logicFill->sendCustomerResultMail( $fillId );
//			$this->logicFill->sendManagerResultMails( $fillId );
//			$this->logicFill->applyTransfers( $fillId );
		}
		return TRUE;
	}

	protected function importMessageAttachment( $importRule,  $part ): bool
	{
		$this->out( 'Attachment Part:' );
		$this->out( '- File Name: '.$part->getFileName() );
		$this->out( '- File Size: '.Alg_UnitFormater::formatBytes( $part->getFileSize() ) );
		$this->out( '- MIME Type: '.$part->getMimeType() );
		$this->out( str_repeat( '-', 76 ) );

		$fileName	= $part->getFileName();
		$ext		= pathinfo( $fileName, PATHINFO_EXTENSION );
		$this->out( '- found attachment: '.$fileName );
//		if( file_exists( $this->path.$fileName ) )
//			unlink( $this->path.$fileName );
		if( strtolower( $ext ) !== 'csv' ){
			$this->out( '  - skipped: not a report: '.$fileName );
			return FALSE;
		}
		try{
			$tempFile	= tempnam( sys_get_temp_dir(), 'import' );
			$sourceFile	= $this->path.$fileName;
			file_put_contents( $tempFile, $part->getContent() );
			$result		= $this->importCsvFile( $importRule, $tempFile );
			$this->out( '- Import Result: '.$result );
			@unlink( $tempFile );
			return TRUE;
		}
		catch( Exception $e ){
			$this->out( 'Error: '.$e->getMessage() );
			return FALSE;
		}
	}
}
/*





INSERT INTO `import_connections` (`importConnectionId`, `importConnectorId`, `creatorId`, `status`, `hostName`, `hostPort`, `hostPath`, `authType`, `authKey`, `authUsername`, `authPassword`, `title`, `description`, `createdAt`, `modifiedAt`) VALUES
(1, 1, 0, 1, 'mail.deutsche-heilpraktikerschule.de\'', 0, '', 1, NULL, 'formular@deutsche-heilpraktikerschule.de', 'hHwk790*', 'Mailbox: Formular', NULL, '1618910097', '1618910097');


INSERT INTO `import_connectors` (`importConnectorId`, `creatorId`, `status`, `type`, `className`, `label`, `description`, `createdAt`, `modifiedAt`) VALUES
(1, 0, 1, 0, 'Logic_Import_Connector_Mailbox', 'Mailbox > Mail > Attachment', '', '1618884309', '1618884309');






*/
