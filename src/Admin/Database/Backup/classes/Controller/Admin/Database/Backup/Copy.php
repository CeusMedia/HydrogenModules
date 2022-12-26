<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_Database_Backup_Copy extends Controller
{
	protected Dictionary $config;
	protected Dictionary $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected Logic_Database_Backup $logicBackup;
	protected Logic_Database_Backup_Copy $logicCopy;

	public function activate( $backupId )
	{
		$backup		= $this->checkBackupId( $backupId );
		$copyPrefix	= $backup->comment['copyPrefix'] ?? '';
		if( strlen( trim( $copyPrefix ) ) ){
			$this->session->set( 'admin-database-backup-copy-prefix', $copyPrefix );
			$this->messenger->noteSuccess( 'Die Kopie der Sicherung wurde aktiviert.' );
		}
		if( ( $from = $this->request->get( 'from' ) ) )
			$this->restart( $from );
		$this->restart( 'admin/database/backup/view/'.$backupId );
	}

	/**
	 *	Creates backup copy in copy database with copy prefix.
	 *	@access		public
	 *	@dodo		...
	 */
	public function create( $backupId )
	{
		$backup		= $this->checkBackupId( $backupId );
		if( !empty( $backup->comment['copyPrefix'] ) ){
			$this->messenger->noteError( 'Eine Kopie dieser Sicherung wurde bereits installiert.' );
			$this->restart( 'view/'.$backupId, TRUE );
		}
		$copyPrefix		= 'copy_'.substr( md5( $backupId ), 16 ).'_';
		$copyDbName		= $this->config->get( 'module.admin_database_backup.copy.database' );
		$defaultDbName	= $this->env->getConfig()->get( 'module.resource_database.access.name' );
		$dbName			= $copyDbName ?: $defaultDbName;
		try{
			$this->logicBackup->load( $backupId, $copyDbName, $copyPrefix );
			$this->logicBackup->storeDataInComment( $backupId, array(
				'copyBackupId'	=> $backupId,
				'copyDatabase'	=> $dbName,
				'copyPrefix'	=> $copyPrefix,
				'copyTimestamp'	=> time(),
			) );
			$this->messenger->noteSuccess( 'Die Kopie der Sicherung wurde installiert.' );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage() );
		}
		if( $from = $this->request->get( 'from' ) )
			$this->restart( $from );
		$this->restart( 'admin/database/backup/view/'.$backupId );
	}

	public function deactivate( $backupId )
	{
		$database	= $this->env->getDatabase();
		$backup		= $this->checkBackupId( $backupId );
		$copyPrefix	= $this->session->get( 'admin-database-backup-copy-prefix' );
		if( $copyPrefix && isset( $backup->comment['copyPrefix'] ) ){
			if( $backup->comment['copyPrefix'] === $copyPrefix ){
				$dbName	= $this->config->get( 'module.resource_database.access.name' );
				try{
					$database->setName( $dbName );
					$this->session->remove( 'admin-database-backup-copy-prefix' );
					$this->messenger->clear();
					$this->messenger->noteSuccess( 'Switching back to default database.' );
				}
				catch( Exception $e ){
					$this->messenger->noteFailure( 'Switching to database "'.$dbName.'" failed.' );
				}
			}
		}
		if( $from = $this->request->get( 'from' ) )
			$this->restart( $from );
		$this->restart( 'admin/database/backup/view/'.$backupId );
	}

	public function drop( $backupId )
	{
		$backup		= $this->checkBackupId( $backupId );
		$database	= $this->env->getDatabase();
		$copyPrefix	= $this->session->get( 'admin-database-backup-copy-prefix' );
		$dbName		= $this->config->get( 'module.admin_database_backup.copy.database' );

		if( empty( $backup->comment['copyPrefix'] ) ){
			$this->messenger->noteError( 'Es wurde bisher keine Kopie dieser Sicherung installiert.' );
			$this->restart( 'view/'.$backupId, TRUE );
		}
		if( $backup->comment['copyPrefix'] == $copyPrefix ){
			$this->messenger->noteError( 'Die Kopie ist noch aktiviert und kann daher nicht gelöscht werden.' );
			$this->restart( 'view/'.$backupId, TRUE );
		}
		$currentDbName	= $database->getName();

		if( $dbName && $currentDbName != $dbName )
			$database->setName( $dbName );

		$tables	= $database->getTables( $backup->comment['copyPrefix'] );
		foreach( $tables as $tableName )
			$database->query( 'DROP TABLE `'.$tableName.'`;' );
		$this->logicBackup->storeDataInComment( $backupId, [
			'copyBackupId'	=> NULL,
			'copyDatabase'	=> NULL,
			'copyPrefix'	=> NULL,
			'copyTimestamp'	=> NULL,
		] );
		$this->messenger->noteSuccess( 'Die Kopie der Sicherung wurde entfernt <small>('.count( $tables ).' Tabellen entfernt)</small>.' );
		if( $currentDbName != $dbName )
			$database->setName( $currentDbName );
		if( $from = $this->request->get( 'from' ) )
			$this->restart( $from );
		$this->restart( 'admin/database/backup/view/'.$backupId );
	}

	//  --  PROTECTED  --  //

	protected function checkBackupId( $backupId )
	{
		$backup	= $this->logicBackup->check( $backupId, FALSE );
		if( FALSE !== $backup )
			return $backup;
		$this->messenger->noteError( 'Invalid backup ID' );
		$this->restart( 'admin/database/backup' );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();

		$this->logicBackup	= Logic_Database_Backup::getInstance( $this->env );
		$this->logicCopy	= Logic_Database_Backup_Copy::getInstance( $this->env );

		$this->moduleConfig	= $this->config->getAll( 'module.admin_database_backup.', TRUE );

		if( !$this->env->getModules()->has( 'Resource_Database' ) ){
			$this->messenger->noteError( 'Kein Datenbank-Modul vorhanden.' );
			$this->restart();
		}
	}
}
