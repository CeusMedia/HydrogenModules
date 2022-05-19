<?php
class Logic_Database_Backup_Copy extends CMF_Hydrogen_Logic
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
