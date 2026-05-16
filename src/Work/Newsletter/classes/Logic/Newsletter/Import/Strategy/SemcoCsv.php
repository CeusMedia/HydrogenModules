<?php

use CeusMedia\Common\FS\File\CSV\Iterator as CsvIterator;

class Logic_Newsletter_Import_Strategy_SemcoCsv extends Logic_Newsletter_Import_AbstractStrategy
{
	protected Logic_Newsletter_Editor $logic;

	public function import( string $filePath, int|string $groupId ): int
	{
		$counter	= 0;
		$iterator	= new CsvIterator( $filePath, TRUE );
		while( $iterator->valid() ){
			$entry	= $iterator->current();
			$entity = Logic_Newsletter_Import_Strategy_SemcoSpreadsheet::convertSpreadsheetRowToReaderEntity( $entry );
			$this->tryToAddReaderAndAssignToGroup( $entity, $groupId );
			$counter++;
			$iterator->next();
		}
		return $counter;
	}
}
