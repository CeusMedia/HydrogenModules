<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_Database_Backup_Copy extends Logic
{
	protected Logic_Database_Backup $logicBackup;

	public function check( string $id, bool $strict = TRUE ): ?object
	{
		return $this->logicBackup->check( $id, $strict );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicBackup	= Logic_Database_Backup::getInstance( $this->env );
	}
}
