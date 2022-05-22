<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_Admin_Database_Backup_Copy extends CMF_Hydrogen_Hook
{
	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@return		void
	 */
	public static function onPageApplyModules( Environment $env, $context, $module, $payload = [] )
	{
		$database		= $env->getDatabase();
		$copyPrefix		= $env->getSession()->get( 'admin-database-backup-copy-prefix' );
		$copyDbName		= $env->getConfig()->get( 'module.admin_database_backup.copy.database' );
		if( $copyPrefix ){
			try{
				if( $copyDbName && $database->getName() !== $copyDbName )
					$database->setName( $copyDbName );
				$database->setPrefix( $copyPrefix );
			}
			catch( Exception $e ){
				$dbName	= $copyDbName ? $copyDbName : $database->getName();
				$env->getMessenger()->noteFailure( 'Switching to database prefix "'.$dbName.' > '.$copyPrefix.'" failed: '.$e->getMessage() );
			}
		}
	}

	/**
	 *	Shows panel on top with note of activated copy database.
	 *	@static
	 *	@access		public
	 *	@return		void
	 */
	public static function onPageBuild( Environment $env, $context, $module, $payload = [] )
	{
		$defaultDbName	= (string) $env->getConfig()->get( 'module.resource_database.access.name' );
		$defaultPrefix	= (string) $env->getConfig()->get( 'module.resource_database.access.prefix' );
		$copyDbName		= (string) $env->getConfig()->get( 'module.admin_database_backup.copy.database' );
		$copyPrefix		= (string) $env->getSession()->get( 'admin-database-backup-copy-prefix' );
		$dbName			= $copyDbName ? $copyDbName : $defaultDbName;
		if( $defaultPrefix !== $copyPrefix ){
			$prefix	= $copyPrefix ? $copyPrefix : $defaultPrefix;
			$env->getMessenger()->noteNotice( '<strong><big>Dieser Datenbestand ist nur eine Kopie.</big></strong><br/>Datenbank: '.$dbName.' | Präfix: '.$prefix.'' );
		}
	}
}
