<?php
class Controller_Admin_Backup_Database extends CMF_Hydrogen_Controller{

	protected $moduleConfig;
	protected $dumps;
	protected $prefixPlaceholder	= '<%?prefix%>';

	public function __onInit(){
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->config->getAll( 'module.admin_backup_database.', TRUE );
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
		$this->addData( 'dumps', $this->dumps );
	}

	public function download( $id ){
		$dump	= $this->check( $id );
		Net_HTTP_Download::sendFile( $dump->pathname, $dump->filename, TRUE );
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

	protected function load( $id ){
		$dump	= $this->check( $id );
		if( !is_readable( $dump->pathname ) )
			throw new RuntimeException( 'Missing read access to SQL script' );

		$dbc		= $this->env->getDatabase();
		$dba		= $this->config->getAll( 'module.resource_database.access.', TRUE );
		$prefix		= $dba->get( 'prefix' );

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
			escapeshellarg( $dba->get( 'name' ) ),										//  configured database name as escaped shell arg
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
			if( !preg_match( '/\.sql$/', $entry->getFilename() ) )
				continue;
			$id			= base64_encode( $entry->getFilename() );
			$timestamp	= filemtime( $entry->getPathname() );

			$comment	= '';
			if( array_key_exists( $id, $this->comments ) )
				$comment	= $this->comments[$id];

			$list[$timestamp]	= (object) array(
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

	public function view( $id ){
		$dump	= $this->check( $id );
		$this->addData( 'dump', $this->check( $id ) );
	}
}
?>
