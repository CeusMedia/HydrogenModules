<?php

use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetLoader;

class Logic_Newsletter_Import_Strategy_SemcoSpreadsheet extends Logic_Newsletter_Import_AbstractStrategy
{
	protected Logic_Newsletter_Editor $logic;
//	protected Entity_Newsletter_Group|int|string $group;

	public function import( string $filePath, int|string $groupId ): int
	{
		$spreadsheet	= SpreadsheetLoader::load( $filePath );

		$sheet	= $spreadsheet->getActiveSheet();
		$rows	= $sheet->getRowIterator();

		$header		= [];
		$counter	= 0;
		foreach( $rows as $rowIndex => $row ){
			$cellIterator	= $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells( FALSE );
			$rowData	= [];
			foreach( $cellIterator as $cell )
				$rowData[]	= trim( (string) $cell->getValue() );

			if( 1 === $rowIndex ){						//  first line -> headers
				$header	= $rowData;
				continue;
			}

			$rowMap		= array_combine( $header, $rowData );
			$entity		= $this->convertSpreadsheetRowToReaderEntity( $rowMap );
			$this->tryToAddReaderAndAssignToGroup( $entity, $groupId );
			$counter++;
		}
		return $counter;
	}

	protected function tryToAddReaderAndAssignToGroup( Entity_Newsletter_Reader $entity, int|string $groupId ): int|string
	{
		$readerId	= $this->logicDeduplication->findReader( $entity );								//  try to find existing reader
		if( -4 === $readerId ){																		//  reader not registered yet
			$readerId	= $this->logic->addReader( $entity );										//  add to database
			if( 0 !== (int) $groupId )																//  group to assign is set
				$this->logic->addReaderToGroup( $readerId, $this->groupId );						//  add reader to group
			return $readerId;
		}
		if( $readerId > 0 ){
			if( 0 !== (int) $groupId )																//  group to assign is set
				if( 0 === $this->logicDeduplication->findReaderGroup( $readerId, $this->groupId ) )	//  group not assigned yet
					$this->logic->addReaderToGroup( $readerId, $this->groupId );					//  add reader to group
			return $readerId;
		}
		return 0;
	}

	/**
	 * @param array $row
	 * @return Entity_Newsletter_Reader
	 * @todo implement status
	 */
	protected function convertSpreadsheetRowToReaderEntity( array $row ): Entity_Newsletter_Reader
	{
		$entity	= new Entity_Newsletter_Reader();
		$entity->firstname		= $row['Vorname'];
		$entity->surname		= $row['Nachname'];
		$entity->email			= $row['E-Mail-Adresse'];
		$entity->registeredAt	= time();
		$entity->gender			= match( $row['Anrede'] ){
			'Herr'	=> Model_Newsletter_Reader::GENDER_MALE,
			'Frau'	=> Model_Newsletter_Reader::GENDER_FEMALE,
			default	=> Model_Newsletter_Reader::GENDER_OTHERS,
		};

		// @todo map status from row value
		$entity->status		= Model_Newsletter_Reader::STATUS_CONFIRMED;

		return $entity;
	}
}