<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_Database_Backup_Copy extends Controller
{
	protected Dictionary $config;
	protected Request $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected Logic_Database_Backup $logicBackup;
	protected Logic_Database_Backup_Copy $logicCopy;
	protected object $words;

	public function activate( string $backupId ): void
	{
		$backup		= $this->checkBackupId( $backupId );
		$copyPrefix	= $backup->comment['copyPrefix'] ?? '';
		if( strlen( trim( $copyPrefix ) ) ){
			$this->session->set( 'admin-database-backup-copy-prefix', $copyPrefix );
			$this->messenger->noteSuccess( $this->words->successActivated );
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
	public function create( string $backupId ): void
	{
		$backup		= $this->checkBackupId( $backupId );
		if( !empty( $backup->comment['copyPrefix'] ) ){
			$this->messenger->noteError( $this->words->errorAlreadyInstalled );
			$this->restart( 'view/'.$backupId, TRUE );
		}
		$copyPrefix		= 'copy_'.substr( md5( $backupId ), 16 ).'_';
		$copyDbName		= $this->config->get( 'module.admin_database_backup.copy.database' );
		$defaultDbName	= $this->env->getConfig()->get( 'module.resource_database.access.name' );
		$dbName			= $copyDbName ?: $defaultDbName;
		try{
			$this->logicBackup->load( $backupId, $copyDbName, $copyPrefix );
			$this->logicBackup->storeDataInComment( $backupId, [
				'copyBackupId'	=> $backupId,
				'copyDatabase'	=> $dbName,
				'copyPrefix'	=> $copyPrefix,
				'copyTimestamp'	=> time(),
			] );
			$this->messenger->noteSuccess( $this->words->successInstalled );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage() );
		}
		if( $from = $this->request->get( 'from' ) )
			$this->restart( $from );
		$this->restart( 'admin/database/backup/view/'.$backupId );
	}

	public function deactivate( string $backupId ): void
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
					$this->messenger->noteSuccess( $this->words->successDeactivated );
				}
				catch( Exception $e ){
					$this->messenger->noteFailure( $this->words->failureSwitchDatabase, $dbName );
				}
			}
		}
		if( $from = $this->request->get( 'from' ) )
			$this->restart( $from );
		$this->restart( 'admin/database/backup/view/'.$backupId );
	}

	public function drop( string $backupId ): void
	{
		$backup		= $this->checkBackupId( $backupId );
		$database	= $this->env->getDatabase();
		$copyPrefix	= $this->session->get( 'admin-database-backup-copy-prefix' );
		$dbName		= $this->config->get( 'module.admin_database_backup.copy.database' );

		if( empty( $backup->comment['copyPrefix'] ) ){
			$this->messenger->noteError( $this->words->errorNotInstalled );
			$this->restart( 'view/'.$backupId, TRUE );
		}
		if( $backup->comment['copyPrefix'] == $copyPrefix ){
			$this->messenger->noteError( $this->words->errorStillActivated );
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
		$this->messenger->noteSuccess( $this->words->successRemoved, count( $tables ) );
		if( $currentDbName != $dbName )
			$database->setName( $currentDbName );
		if( $from = $this->request->get( 'from' ) )
			$this->restart( $from );
		$this->restart( 'admin/database/backup/view/'.$backupId );
	}

	//  --  PROTECTED  --  //

	protected function checkBackupId( string $backupId )
	{
		$backup	= $this->logicBackup->check( $backupId, FALSE );
		if( FALSE !== $backup )
			return $backup;
		$this->messenger->noteError( $this->words->errorInvalidId );
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

		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicBackup	= Logic_Database_Backup::getInstance( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicCopy	= Logic_Database_Backup_Copy::getInstance( $this->env );

		$this->moduleConfig	= $this->config->getAll( 'module.admin_database_backup.', TRUE );
		$this->words		= (object) $this->getWords( 'msg.copy', 'admin/database/backup' );

		if( !$this->env->getModules()->has( 'Resource_Database' ) ){
			$this->messenger->noteError( $this->words->errorNoDatabaseModule );
			$this->restart();
		}
	}
}
