<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_Database_Backup extends Controller
{
	protected Dictionary $config;
	protected Request $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected Logic_Database_Backup $logicBackup;

	public function backup(): void
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

	public function index(): void
	{
		$prefix		= $this->session->get( 'admin-database-backup-copy-prefix' );
		$this->addData( 'backups', $this->logicBackup->index() );
		$this->addData( 'currentCopyPrefix', $prefix );
	}

	/**
	 *	@param		string		$id
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function download( string $id ): void
	{
		/** @var Logic_Authentication $logicAuth */
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$userId			= $logicAuth->getCurrentUserId();
		if( !$logicAuth->checkPassword( $userId, $this->request->get( 'password', '' ) ) ){
			$this->messenger->noteError( 'Das Passwort stimmt nicht.' );
			$this->restart( 'view/'.$id, TRUE );
		}
		$backup	= $this->check( $id );
		HttpDownload::sendFile( $backup->pathname, $backup->filename );
	}

	public function remove( string $id ): void
	{
		$backup	= $this->check( $id );
		$this->logicBackup->remove( $id );
		$this->messenger->noteSuccess( 'Die Sicherung "%s" wurde entfernt.', $backup->filename );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		string		$id
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function restore( string $id ): void
	{
		/** @var Logic_Authentication $logicAuth */
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$userId			= $logicAuth->getCurrentUserId();
		if( !$logicAuth->checkPassword( $userId, $this->request->get( 'password', '' ) ) ){
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

	public function view( string $id ): void
	{
		$backup		= $this->check( $id );
		$prefix		= $this->env->getSession()->get( 'admin-database-backup-copy-prefix' );
		$this->addData( 'backup', $backup );
		$this->addData( 'currentCopyPrefix', $prefix );
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
		$this->moduleConfig	= $this->config->getAll( 'module.admin_database_backup.', TRUE );

		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
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
