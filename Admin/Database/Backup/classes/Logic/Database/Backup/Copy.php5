<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_Database_Backup_Copy extends Logic
{
	protected $logicBackup;

	public function check( $id, $strict = TRUE )
	{
		return $this->logicBackup->check( $id, $strict );
	}

	protected function __onInit()
	{
		$this->logicBackup	= Logic_Database_Backup::getInstance( $this->env );
	}
}
