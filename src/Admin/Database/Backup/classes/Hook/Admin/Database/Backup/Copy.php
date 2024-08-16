<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Admin_Database_Backup_Copy extends Hook
{
	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$database		= $this->env->getDatabase();
		$copyPrefix		= $this->env->getSession()->get( 'admin-database-backup-copy-prefix' );
		$copyDbName		= $this->env->getConfig()->get( 'module.admin_database_backup.copy.database' );
		if( $copyPrefix ){
			try{
				if( $copyDbName && $database->getName() !== $copyDbName )
					$database->setName( $copyDbName );
				$database->setPrefix( $copyPrefix );
			}
			catch( Exception $e ){
				$dbName	= $copyDbName ?: $database->getName();
				$this->env->getMessenger()->noteFailure( 'Switching to database prefix "'.$dbName.' > '.$copyPrefix.'" failed: '.$e->getMessage() );
			}
		}
	}

	/**
	 *	Shows panel on top with note of activated copy database.
	 *	@static
	 *	@access		public
	 *	@return		void
	 */
	public function onPageBuild(): void
	{
		$defaultDbName	= (string) $this->env->getConfig()->get( 'module.resource_database.access.name' );
		$defaultPrefix	= (string) $this->env->getConfig()->get( 'module.resource_database.access.prefix' );
		$copyDbName		= (string) $this->env->getConfig()->get( 'module.admin_database_backup.copy.database' );
		$copyPrefix		= (string) $this->env->getSession()->get( 'admin-database-backup-copy-prefix' );
		$dbName			= $copyDbName ?: $defaultDbName;
		if( $defaultPrefix !== $copyPrefix ){
			$prefix		= $copyPrefix ?: $defaultPrefix;
			$message	= '<strong><big>Dieser Datenbestand ist nur eine Kopie.</big></strong><br/>Datenbank: %s | Präfix: %s';
			$this->env->getMessenger()->noteNotice( vsprintf( $message, [$dbName, $prefix] ) );
		}
	}
}
