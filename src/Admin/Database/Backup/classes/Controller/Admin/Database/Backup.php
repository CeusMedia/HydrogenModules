<?php

use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Admin_Database_Backup extends Controller
{
	protected $config;
	protected $request;
	protected $session;
	protected $messenger;
	protected $moduleConfig;
	protected $logicBackup;

	public function backup()
	{
		if( $this->request->has( 'save' ) ){
			try{
				$comment	= $this->request->get( 'comment' );
				$backupId	= $this->logicBackup->dump( $comment );
				$this->messenger->noteSuccess( 'Die Sicherung wurde erstellt.' );
				$this->restart( 'view/'.$backupId, TRUE );
			}
			catch( Exception $e ){
				$this->messenger->noteFailure( $e->getMessage() );
			}
		}
		$this->addData( 'path', $this->path );
	}

	public function index()
	{
		$prefix		= $this->session->get( 'admin-database-backup-copy-prefix' );
		$this->addData( 'backups', $this->logicBackup->index() );
		$this->addData( 'currentCopyPrefix', $prefix );
	}

	public function download( $id )
	{
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$userId			= $logicAuth->getCurrentUserId();
		if( !$logicAuth->checkPassword( $userId, $this->request->get( 'password' ) ) ){
			$this->messenger->noteError( 'Das Passwort stimmt nicht.' );
			$this->restart( 'view/'.$id, TRUE );
		}
		$backup	= $this->check( $id );
		HttpDownload::sendFile( $backup->pathname, $backup->filename );
	}

	public function remove( $id )
	{
		$backup	= $this->check( $id );
		$this->logicBackup->remove( $id );
		$this->messenger->noteSuccess( 'Die Sicherung "%s" wurde entfernt.', $backup->filename );
		$this->restart( NULL, TRUE );
	}

	public function restore( $id )
	{
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$userId			= $logicAuth->getCurrentUserId();
		if( !$logicAuth->checkPassword( $userId, $this->request->get( 'password' ) ) ){
			$this->messenger->noteError( 'Das Passwort stimmt nicht.' );
			$this->restart( 'view/'.$id, TRUE );
		}
		$backup	= $this->check( $id );
		try{
			$this->logicBackup->load( $id );
			$this->messenger->noteSuccess( 'Die Sicherung "%s" wurde wiederhergestellt.', $backup->filename );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage() );
		}
		$this->restart( 'view/'.$id, TRUE );
	}

	public function view( $id )
	{
		$backup		= $this->check( $id );
		$prefix		= $this->env->getSession()->get( 'admin-database-backup-copy-prefix' );
		$this->addData( 'backup', $backup );
		$this->addData( 'currentCopyPrefix', $prefix );
	}

	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->config->getAll( 'module.admin_database_backup.', TRUE );

		$this->logicBackup	= Logic_Database_Backup::getInstance( $this->env );

		if( !$this->env->getModules()->has( 'Resource_Database' ) ){
			$this->messenger->noteError( 'Kein Datenbank-Modul vorhanden.' );
			$this->restart();
		}
	}

	protected function check( string $id ): ?object
	{
		if( ( $backup = $this->logicBackup->check( $id, FALSE ) ) )
			return $backup;
		$this->messenger->noteError( 'Ungültige Sicherungs-ID.' );
		$this->restart( NULL, TRUE );
		return NULL;
	}
}
