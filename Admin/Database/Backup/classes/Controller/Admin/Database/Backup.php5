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
				$filename	= $this->dump();
				$id	= base64_encode( $filename );
				$this->comments[$id]	= $this->request->get( 'comment' );
				\FS_File_JSON_Writer::save( $this->commentsFile, $this->comments );
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
		if( !array_key_exists( $id, $this->dumps ) ){
			$this->messenger->noteError( 'Invalid dump ID' );
			$this->restart( NULL, TRUE );
		}
		return $this->dumps[$id];
	}

	public function index(){
		$prefix		= $this->env->getSession()->get( 'admin-database-backup-copy-prefix' );
		$this->addData( 'dumps', $this->dumps );
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

	protected function dump(){
		$filename	= "dump_".date( "Y-m-d_H:i:s" ).".sql";
		$pathname	= $this->path.$filename;
		$dbc		= $this->env->getDatabase();
		$dba		= $this->config->getAll( 'module.resource_database.access.', TRUE );
		$prefix		= $dba->get( 'prefix' );
		$tables		= '';																	//  no table selection by default
		if( $prefix ){																		//  prefix has been set
			$tables		= array();															//  prepare list of tables matching prefix
			foreach( $dbc->query( "SHOW TABLES LIKE '".$prefix."%'" ) as $table )			//  iterate found tables with prefix
				$tables[]	= escapeshellarg( $table[0] );									//  collect table as escaped shell arg
			$tables	= join( ' ', $tables );													//  reduce tables list to tables arg
		}

		$command	= call_user_func_array( "sprintf", array(								//  call sprintf with arguments list
			"mysqldump -h%s -P%s -u%s -p%s %s %s > %s",										//  command to replace within
			escapeshellarg( $dba->get( 'host' ) ),											//  configured host name as escaped shell arg
			escapeshellarg( $dba->get( 'port' ) ? $dba->get( 'port' ) : 3306  ),			//  configured port as escaped shell arg
			escapeshellarg( $dba->get( 'username' ) ),										//  configured username as escaped shell arg
			escapeshellarg( $dba->get( 'password' ) ),										//  configured password as escaped shell arg
			escapeshellarg( $dba->get( 'name' ) ),											//  configured database name as escaped shell arg
			$tables,																		//  collected found tables
			escapeshellarg( $pathname ),													//  dump output filename
		) );
		$resultCode		= 0;
		$resultOutput	= array();
		exec( $command, $resultOutput, $resultCode );
		if( $resultCode !== 0 ){
			$this->messenger->noteFailure( 'Database dump failed.' );
			$this->restart( NULL, TRUE );
		}

		/*  --  REPLACE PREFIX  --  */
		$regExp		= "@(EXISTS|FROM|INTO|TABLE|TABLES|for table)( `)(".$prefix.")(.+)(`)@U";		//  build regular expression
		$callback	= array( $this, '_callbackReplacePrefix' );										//  create replace callback
		rename( $pathname, $pathname."_" );															//  move dump file to source file
		$fpIn		= fopen( $pathname."_", "r" );													//  open source file
		$fpOut		= fopen( $pathname, "a" );														//  prepare empty target file
		while( !feof( $fpIn ) ){																	//  read input file until end
			$line	= fgets( $fpIn );																//  read line buffer
			$line	= preg_replace_callback( $regExp, $callback, $line );							//  perform replace in buffer
			fwrite( $fpOut, $line );																//  write buffer to target file
		}
		fclose( $fpOut );																			//  close target file
		fclose( $fpIn );																			//  close source file
		unlink( $pathname."_" );
		return $filename;
	}

	protected function load( $id, $dbName, $prefix = NULL ){
		$dump	= $this->check( $id );
		if( !is_readable( $dump->pathname ) )
			throw new RuntimeException( 'Missing read access to SQL script' );

		$dbc		= $this->env->getDatabase();
		$dba		= $this->config->getAll( 'module.resource_database.access.', TRUE );
		$dbName		= $dbName ? $dbName : $dba->get( 'name' );
		$prefix		= $prefix ? $prefix : $dba->get( 'prefix' );

		$tempName	= $dump->pathname.".tmp";
		$fpIn		= fopen( $dump->pathname, "r" );									//  open source file
		$fpOut		= fopen( $tempName, "a" );											//  prepare empty target file
		while( !feof( $fpIn ) ){														//  read input file until end
			$line	= fgets( $fpIn );													//  read line buffer
			$line	= str_replace( "<%?prefix%>", $prefix, $line );						//  replace table prefix placeholder
			fwrite( $fpOut, $line );													//  write buffer to target file
		}
		fclose( $fpOut );																//  close target file
		fclose( $fpIn );																//  close source file
		$command	= call_user_func_array( "sprintf", array(							//  call sprintf with arguments list
			"mysql -h%s -P%s -u%s -p%s %s < %s",										//  command to replace within
			escapeshellarg( $dba->get( 'host' ) ),										//  configured host as escaped shell arg
			escapeshellarg( $dba->get( 'port' ) ? $dba->get( 'port' ) : 3306 ),			//  configured port as escaped shell arg
			escapeshellarg( $dba->get( 'username' ) ),									//  configured username as escaped shell arg
			escapeshellarg( $dba->get( 'password' ) ),									//  configured pasword as escaped shell arg
			escapeshellarg( $dbName ),													//  configured database name as escaped shell arg
			escapeshellarg( $tempName ),												//  temp file name as escaped shell arg
		) );
		exec( $command );
		unlink( $tempName );
	}

	protected function readIndex(){
		$list	= array();
		$map	= array();
		$index	= new DirectoryIterator( $this->path );
		foreach( $index as $entry ){
			if( $entry->isDir() || $entry->isDot() )
				continue;
			if( !preg_match( '/^dump_.+\.sql$/', $entry->getFilename() ) )
				continue;
			$id			= base64_encode( $entry->getFilename() );

			$timestamp	= preg_replace( '/[a-z_]/', ' ', $entry->getFilename() );
			$timestamp	= strtotime( rtrim( trim( $timestamp ), '.' ) );
			if( !$timestamp )
				$timestamp	= filemtime( $entry->getPathname() );

			$comment	= '';
			if( array_key_exists( $id, $this->comments ) )
				$comment	= $this->comments[$id];

			$list[$timestamp.uniqid()]	= (object) array(
				'id'			=> $id,
				'filename'		=> $entry->getFilename(),
				'pathname'		=> $entry->getPathname(),
				'filesize'		=> filesize( $entry->getPathname() ),
				'timestamp'		=> $timestamp,
				'comment'		=> $comment,
			);
		}
		krsort( $list );
		foreach( $list as $item )
			$map[$item->id]	= $item;
		return $map;
	}

	public function remove( $id ){
		$dump	= $this->check( $id );
		@unlink( $dump->pathname );
		if( array_key_exists( $id, $this->comments ) ){
			unset( $this->comments[$id] );
			\FS_File_JSON_Writer::save( $this->commentsFile, $this->comments );
		}
		$this->messenger->noteSuccess( 'Database dump "%s" removed.', $dump->filename );
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
			$this->load( $id );
			$this->messenger->noteSuccess( 'Database dump "%s" imported.', $dump->filename );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage() );
		}
		$this->restart( 'view/'.$id, TRUE );
	}

	protected function storeDataInComment( $id, $data ){
		$dump	= $this->check( $id );
		if( !array_key_exists( $id, $this->comments ) )
			$this->comments[$id]	= array( 'comment' => '' );
		if( is_string( $this->comments[$id] ) )
			$this->comments[$id]	= array( 'comment' => $dump->comment );
		foreach( $data as $key => $value ){
			if( is_null( $value ) && isset( $this->comments[$id][$key] ) )
				unset( $this->comments[$id][$key] );
			else
				$this->comments[$id][$key]	= $value;
		}
		\FS_File_JSON_Writer::save( $this->commentsFile, $this->comments );
	}

	public function view( $id ){
		$dump	= $this->check( $id );
		$prefix		= $this->env->getSession()->get( 'admin-database-backup-copy-prefix' );
		$this->addData( 'dump', $this->check( $id ) );
		$this->addData( 'currentCopyPrefix', $prefix );
	}
}
?>
