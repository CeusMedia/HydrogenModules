<?php

use CeusMedia\Common\FS\File\CSV\Iterator as CsvIterator;

class Logic_Newsletter_Import_Strategy_OwnCsv extends Logic_Newsletter_Import_AbstractStrategy
{
	protected Logic_Newsletter_Editor $logic;

	public function import( string $filePath, int|string $groupId ): int
	{
		$counter	= 0;
		$iterator	= new CsvIterator( $filePath, TRUE );
		while( $iterator->valid() ){
			$entry	= $iterator->current();
			if( !isset( $entry['email'] ) )
				throw new RuntimeException( 'Invalid format. Columns must be: email, firstname, surname, gender' );
			$entity	= Entity_Newsletter_Reader::fromArray( $entry );
			$this->tryToAddReaderAndAssignToGroup( $entity, $groupId );
			$counter++;
			$iterator->next();
		}
		return $counter;
	}
}
