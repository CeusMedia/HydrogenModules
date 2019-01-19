<?php
class Controller_Admin_Database_Backup extends CMF_Hydrogen_Controller{

	protected $moduleConfig;
	protected $dumps;
	protected $prefixPlaceholder	= '<%?prefix%>';

	public function __onInit(){
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->config->getAll( 'module.admin_database_backup.', TRUE );
		$this->path			= $this->moduleConfig->get( 'path' );
		$this->commentsFile	= $this->path.'comments.json';
		if( !file_exists( $this->path ) )
			\FS_Folder_Editor::createFolder( $this->path );
		if( !file_exists( $this->commentsFile ) )
			file_put_contents( $this->commentsFile, '[]' );
		$this->comments	= \FS_File_JSON_Reader::load( $this->commentsFile, TRUE );

		if( !$this->env->getModules()->has( 'Resource_Database' ) ){
			$this->messenger->noteError( 'Kein Datenbank-Modul vorhanden' );
			$this->restart();
		}
		$this->dumps	= $this->readIndex();
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@return		void
	 *	@todo		export to hook class
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
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
	 *	@todo		implement hook and export to hook class
	 */
	static public function onPageBuild( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
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

	/**
	 *	Creates dump copy in copy database with copy prefix.
	 *	@access		public
	 *	@dodo		...
	 */
	public function createCopy( $id ){
		$dump			= $this->check( $id );
		if( $dump->comment['copyPrefix'] ){
			$this->messenger->noteError( 'Eine Kopie dieser Sicherung wurde bereits installiert.' );
			$this->restart( 'view/'.$id, TRUE );
		}
		$copyPrefix		= 'copy_'.substr( md5( $dump->id ), 16 ).'_';
		$copyDbName		= $this->config->get( 'module.admin_database_backup.copy.database' );
		$defaultDbName	= $this->env->config->get( 'module.resource_database.access.name' );
		$dbName			= $copyDbName ? $copyDbName : $defaultDbName;
		try{
			$this->load( $id, $copyDbName, $copyPrefix );
			$this->storeDataInComment( $id, array(
				'copyDumpId'	=> $id,
				'copyDatabase'	=> $dbName,
				'copyPrefix'	=> $prefix,
				'copyTimestamp'	=> time(),
			) );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage );
		}
		$this->restart( 'view/'.$id, TRUE );
	}

	public function activateCopy( $id ){
		$dump	= $this->check( $id );
		$prefix	= isset( $dump->comment['copyPrefix'] ) ? $dump->comment['copyPrefix'] : NULL;
		if( strlen( trim( $prefix ) ) ){
			$this->session->set( 'admin-database-backup-copy-prefix', $prefix );
		}
		$this->restart( 'view/'.$id, TRUE );
	}

	public function deactivateCopy( $id ){
		$database	= $this->env->getDatabase();
		$dump		= $this->check( $id );
		$prefix		= $this->session->get( 'admin-database-backup-copy-prefix' );
		if( $prefix && isset( $dump->comment['copyPrefix'] ) ){
			if( $dump->comment['copyPrefix'] === $prefix ){
				try{
					$efaultDbName	= $this->config->get( 'module.resource_database.access.name' );
					$database->setName( $efaultDbName );
					$prefix	= $this->session->remove( 'admin-database-backup-copy-prefix' );
					$this->messenger->clear();
					$this->messenger->noteSuccess( 'Switching back to default database.' );
				}
				catch( Exception $e ){
					$this->messenger->noteFailure( 'Switching to database "'.$dbName.'" failed.' );
				}
			}
		}
		$this->restart( 'view/'.$id, TRUE );
	}

	public function dropCopy( $id ){
		$dump		= $this->check( $id );
		$database	= $this->env->getDatabase();
		$prefix		= $this->session->get( 'admin-database-backup-copy-prefix' );
		$dbName		= $this->config->get( 'module.admin_database_backup.copy.database' );
		if( isset( $dump->comment['copyPrefix'] ) && $dump->comment['copyPrefix'] == $prefix ){
			$this->messenger->noteError( 'Die Kopie ist noch aktiviert und kann daher nicht gelöscht werden.' );
			$this->restart( 'view/'.$id, TRUE );
		}
		$currentDbName	= $database->getName();
		if( $currentDbName != $dbName )
			$database->setName( $dbName );
	//	$database->...
		$this->storeDataInComment( $id, array(
			'copyPrefix'	=> NULL,
			'copyDate'		=> NULL,
		) );
		if( $currentDbName != $dbName )
			$database->setName( $currentDbName );
		$this->restart( 'view/'.$id, TRUE );
	}


	protected function _callbackReplacePrefix( $matches ){
		if( $matches[1] === 'for table' )
			return $matches[1].$matches[2].$matches[4].$matches[5];
		return $matches[1].$matches[2].$this->prefixPlaceholder.$matches[4].$matches[5];
	}

	public function backup(){
		if( $this->request->has( 'save' ) ){
			try{
				$filename	= $this->logicBackup->dump();
				$id	= base64_encode( $filename );
				$this->logicBackup->storeDataInComment( array( 'comment' => $this->request->get( 'comment' ) ) );
				$this->messenger->noteSuccess( 'Database dump "%s" created.', $filename );
				$this->restart( 'view/'.$id, TRUE );
			}
			catch( Exception $e ){
				$this->messenger->noteFailure( $e->getMessage() );
			}
		}
		$this->addData( 'path', $this->path );
	}

	protected function check( $id ){
		if( ( $dump = $this->logicBackup->check( $id, FALSE ) ) )
			return $dump;
		$this->messenger->noteError( 'Ungültige Sicherungs-ID.' );
		$this->restart( NULL, TRUE );
	}

	public function index(){
		$prefix		= $this->session->get( 'admin-database-backup-copy-prefix' );
		$this->addData( 'dumps', $this->logicBackup->index() );
		$this->addData( 'currentCopyPrefix', $prefix );
	}

	public function download( $id ){
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$userId			= $logicAuth->getCurrentUserId();
		if( !$logicAuth->checkPassword( $userId, $this->request->get( 'password' ) ) ){
			$this->messenger->noteError( 'Das Passwort stimmt nicht.' );
			$this->restart( 'view/'.$id, TRUE );
		}
		$dump	= $this->check( $id );
		\Net_HTTP_Download::sendFile( $dump->pathname, $dump->filename, TRUE );
	}

	public function remove( $id ){
		$dump	= $this->check( $id );
		$this->logicBackup->remove( $id );
		$this->messenger->noteSuccess( 'Die Sicherung "%s" wurde entfernt.', $dump->filename );
		$this->restart( NULL, TRUE );
	}

	public function restore( $id ){
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$userId			= $logicAuth->getCurrentUserId();
		if( !$logicAuth->checkPassword( $userId, $this->request->get( 'password' ) ) ){
			$this->messenger->noteError( 'Das Passwort stimmt nicht.' );
			$this->restart( 'view/'.$id, TRUE );
		}
		$dump	= $this->check( $id );
		try{
			$this->logicBackup->load( $id );
			$this->messenger->noteSuccess( 'Die Sicherung "%s" wurde wiederhergestellt.', $dump->filename );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage() );
		}
		$this->restart( 'view/'.$id, TRUE );
	}

	public function view( $id ){
		$dump		= $this->check( $id );
		$prefix		= $this->env->getSession()->get( 'admin-database-backup-copy-prefix' );
		/*   @todo remove this fallback */
		if( is_string( $dump->comment ) ){
			$dump->comment	= array(
				'comment'		=> $dump->comment,
				'copyPrefix'	=> NULL,
				'copyTimestamp'	=> NULL,
			);
		}
		$this->addData( 'dump', $dump );
		$this->addData( 'currentCopyPrefix', $prefix );
	}
}
?>
