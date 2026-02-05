<?php

use CeusMedia\HydrogenFramework\Logic\Shared;

abstract class Logic_Newsletter_Import_AbstractStrategy extends Shared
{
	protected Logic_Newsletter_Editor $logic;
	protected Logic_Newsletter_Import_Deduplication $logicDeduplication;
	protected int|string $groupId	= 0;

	abstract public function import( string $filePath, int|string $groupId ): int;

	protected function tryToAddReaderAndAssignToGroup( Entity_Newsletter_Reader $entity, int|string $groupId ): int|string
	{
		$readerId	= $this->logicDeduplication->findReader( $entity );								//  try to find existing reader
		if( -4 === $readerId ){																		//  reader not registered yet
			$readerId	= $this->logic->addReader( $entity );										//  add to database
			if( 0 !== (int) $groupId )																//  group to assign is set
				$this->logic->addReaderToGroup( $readerId, $groupId );								//  add reader to group
			return $readerId;
		}
		else if( $readerId > 0 ){																		//  reader exists
			if( 0 !== (int) $groupId )																//  group to assign is set
				if( 0 === $this->logicDeduplication->findReaderGroup( $readerId, $groupId ) )		//  group not assigned yet
					$this->logic->addReaderToGroup( $readerId, $groupId );							//  add reader to group
			return $readerId;
		}
		return 0;
	}

	protected function __onInit(): void
	{
		$this->logic		= new Logic_Newsletter_Editor( $this->env );
//		$this->modelReader	= new Model_Newsletter_Reader( $this->env );
//		$this->modelAddress	= new Model_Address( $this->env );
		$this->logicDeduplication	= new Logic_Newsletter_Import_Deduplication( $this->env );

	}
}