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

	protected function importCsvFile( $importRule, $filePath, $verbose = FALSE )
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
	protected function importData( $formId, $ruleSet, array $importData, $verbose ): int
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

	protected function importMessageAttachment( $importRule, $part, $verbose = FALSE ): bool
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

$formData['email']	= "mailtest@deutsche-heilpraktikerschule.de";

		$formData['createdAt']	= time();
		if( isset( $formData['creationDateTime'] ) ){
			$formData['createdAt']	= strtotime( $formData['creationDateTime'] );
			unset( $formData['creationDateTime'] );
		}
		return $formData;
	}
}

/*


INSERT INTO `import_connections` (`importConnectionId`, `importConnectorId`, `creatorId`, `status`, `hostName`, `hostPort`, `hostPath`, `authType`, `authKey`, `authUsername`, `authPassword`, `title`, `description`, `createdAt`, `modifiedAt`) VALUES
(1, 1, 0, 1, 'mail.deutsche-heilpraktikerschule.de\'', 0, '', 1, NULL, 'formular@deutsche-heilpraktikerschule.de', 'hHwk790*', 'Mailbox: Formular', NULL, '1618910097', '1618910097');


INSERT INTO `import_connectors` (`importConnectorId`, `creatorId`, `status`, `type`, `className`, `label`, `description`, `createdAt`, `modifiedAt`) VALUES
(1, 0, 1, 0, 'Logic_Import_Connector_Mailbox', 'Mailbox > Mail > Attachment', '', '1618884309', '1618884309');

INSERT INTO `form_import_rules` (`formImportRuleId`, `importConnectionId`, `formId`, `title`, `searchCriteria`, `rules`, `renameTo`, `moveTo`, `createdAt`, `modifiedAt`) VALUES
(1, 1, 8, 'Test-Import', 'SUBJECT \"Targroup Leads Deutsche Heilpraktikerschule\"', '{\r\n	\"map\": {\r\n		\"0\": \"creationDateTime\",\r\n		\"3\": \"leadReferer\",\r\n		\"4\": \"topic\",\r\n		\"6\": \"base\",\r\n		\"7\": \"gender\",\r\n		\"8\": \"firstname\",\r\n		\"9\": \"surname\",\r\n		\"10\": \"email\",\r\n		\"13\": \"city\",\r\n		\"14\": \"postcode\",\r\n		\"50\": \"street\"\r\n	},\r\n	\"create\": {\r\n		\"50\": {\r\n			\"lines\": [\r\n				\"[@11] [@12]\"\r\n			]\r\n		}\r\n	},\r\n	\"set\": {\r\n		\"acceptNews\": \"nein\"\r\n	},\r\n	\"translate\": {\r\n		\"4\": {\r\n			\"Heilpraktiker für Naturheilkunde\": \"hp-nat\",\r\n			\"Heilpraktiker für Psychotherapie\": \"hp-psy\",\r\n			\"Ganzheitlicher Heilpraktiker\": \"both\",\r\n			\"Heilpraktiker für Psychotherapie im Fernlehrgang\": \"fl-hp-psy\",\r\n			\"Heilpraktiker für Naturheilkunde im Fernlehrgang\": \"fl-hp\"\r\n		},\r\n		\"7\": {\r\n			\"Frau\": 0,\r\n			\"Herr\": 1\r\n		}\r\n	},\r\n	\"type\": {\r\n		\"topic\": \"choice\",\r\n		\"base\": \"choice\",\r\n		\"gender\": \"choice\",\r\n		\"firstname\": \"text\",\r\n		\"surname\": \"text\",\r\n		\"street\": \"text\",\r\n		\"city\": \"text\",\r\n		\"country\": \"choice\",\r\n		\"postcode\": \"text\",\r\n		\"street\": \"text\"\r\n	},\r\n	\"label\": {\r\n		\"topic\": \"Interesse an\",\r\n		\"base\": \"Am Standort\",\r\n		\"email\": \"E-Mail-Adresse\",\r\n		\"gender\": \"Geschlecht\",\r\n		\"firstname\": \"Vorname\",\r\n		\"surname\": \"Nachname\",\r\n		\"street\": \"E-Mail\",\r\n		\"city\": \"Ort\",\r\n		\"country\": \"Land\",\r\n		\"postcode\": \"PLZ\",\r\n		\"street\": \"Straße und Nr.\",\r\n		\"acceptNews\":\"Zustimmung zur Zusendung von Informationsmaterial\"\r\n	},\r\n	\"valueLabel\": {\r\n		\"topic-hpnat\": \"Heilpraktiker für Naturheilkunde im Präsenzunterricht\",\r\n		\"topic-hppsy\": \"Heilpraktiker für Psychotherapie im Präsenzunterricht\",\r\n		\"topic-both\": \"Ganzheitlicher Heilpraktiker im Präsenzunterricht\",\r\n		\"topic-fl-hp\": \"Ausbildung zum Heilpraktiker für Naturheilkunde im Fernlehrgang\",\r\n		\"topic-fl-hp-psy\": \"Ausbildung zum Heilpraktiker für Psychotherapie im Fernlehrgang\",\r\n		\"topic-fl-ghp\": \"Ausbildung zum Ganzheitlichen Heilpraktiker im Fernlehrgang\",\r\n		\"topic-fl-gb\": \"Ausbildung zum Gesundheitsberater im Fernlehrgang\",\r\n		\"topic-fl-eba\": \"Ausbildung zum Ernährungsberater im Fernlehrgang\",\r\n		\"topic-fl-kh\": \"Ausbildung Klassische Homöopathie im Fernlehrgang\",\r\n		\"topic-fl-se\": \"Weiterbildung Stressmanagement und Entspannungsverfahren im Fernlehrgang\",\r\n		\"topic-fl-eb\": \"Weiterbildung Ernährungsberatung im Fernlehrgang\",\r\n		\"topic-oa-ap\": \"Online-Ausbildung Akupunktur\",\r\n		\"topic-oa-ph\": \"Online-Ausbildung Phytotherapie\",\r\n		\"base-aschaffenburg\": \"Aschaffenburg\",\r\n		\"base-bamberg\": \"Bamberg\",\r\n		\"base-berlin\": \"Berlin\",\r\n		\"base-bonn\": \"Bonn\",\r\n		\"base-info\": \"Buchholz\",\r\n		\"base-darmstadt\": \"Darmstadt\",\r\n		\"base-hp-nat-dresden\": \"Dresden\",\r\n		\"base-frankfurt-am-main\": \"Frankfurt am Main\",\r\n		\"base-freiburg\": \"Freiburg\",\r\n		\"base-fulda\": \"Fulda\",\r\n		\"base-hannover\": \"Hannover\",\r\n		\"base-koeln\": \"Köln\",\r\n		\"base-leipzig\": \"Leipzig\",\r\n		\"base-bensheim\": \"Mannheim / Bensheim\",\r\n		\"base-myk\": \"Mayen-Koblenz\",\r\n		\"base-muelheim\": \"Mülheim / Ruhr\",\r\n		\"base-muenster\": \"Münster\",\r\n		\"base-rostock\": \"Rostock\",\r\n		\"base-schweiz\": \"Schweiz\",\r\n		\"base-trierwittlich\": \"Trier / Wittlich\",\r\n		\"base-wiesbaden\": \"Wiesbaden\",\r\n		\"base-luedinghausen\": \"Lüdinghausen\",\r\n		\"base-offenbach\": \"Offenbach\",\r\n		\"base-potsdam\": \"Potsdam\",\r\n		\"base-sauerland\": \"Sauerland\",\r\n		\"gender-0\": \"weiblich\",\r\n		\"gender-1\": \"männlich\",\r\n		\"gender-2\": \"inter\"\r\n	}\r\n}\r\n', NULL, NULL, '1612304195', '1612495678');


--  FOR TEST  --
UPDATE `form_rules` SET `mailAddresses`="mailtest@deutsche-heilpraktikerschule.de" WHERE formId=8;
UPDATE `form_transfer_rules` SET `formTransferTargetId`=3 WHERE formId=8;
UPDATE `form_transfer_targets` SET `baseUrl`="mailtest@deutsche-heilpraktikerschule.de,dev@ceusmedia.de" WHERE formTransferTargetId=3;
TRUNCATE form_fills;
TRUNCATE form_fill_transfers;
TRUNCATE mails;





*/
