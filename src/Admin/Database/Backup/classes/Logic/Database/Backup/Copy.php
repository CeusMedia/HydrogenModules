<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_Database_Backup_Copy extends Logic
{
	protected $logicBackup;

	public function check( string $id, bool $strict = TRUE ): ?object
	{
		return $this->logicBackup->check( $id, $strict );
	}

	protected function __onInit(): void
	{
		$this->logicBackup	= Logic_Database_Backup::getInstance( $this->env );
	}
}
