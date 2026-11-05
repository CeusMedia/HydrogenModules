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
		$defaultPrefix	= (string) $this->env->getConfig()->get( 'module.resource_database.access.prefix' );
		$copyPrefix		= $this->env->getSession()->get( 'admin-database-backup-copy-prefix', $defaultPrefix );
		$copyDbName		= $this->env->getConfig()->get( 'module.admin_database_backup.copy.database' );
		if( $copyPrefix ){
			try{
				if( $copyDbName && $database->getName() !== $copyDbName )
					$database->setName( $copyDbName );
				$database->setPrefix( $copyPrefix );
			}
			catch( Exception $e ){
				$dbName	= $copyDbName ?: $database->getName();
				$words	= $this->env->getLanguage()->getSection( 'admin/database/backup', 'hook' );
				$this->env->getMessenger()->noteFailure( vsprintf( $words['failureSwitchPrefix'], [$dbName, $copyPrefix, $e->getMessage()] ) );
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
		$copyPrefix		= (string) $this->env->getSession()->get( 'admin-database-backup-copy-prefix', $defaultPrefix );
		$dbName			= $copyDbName ?: $defaultDbName;
		if( $defaultPrefix !== $copyPrefix ){
			$prefix		= $copyPrefix ?: $defaultPrefix;
			$words		= $this->env->getLanguage()->getSection( 'admin/database/backup', 'hook' );
			$this->env->getMessenger()->noteNotice( vsprintf( $words['noticeCopyActive'], [$dbName, $prefix] ) );
		}
	}
}
