<?php

use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\FS\File\JSON\Reader as JsonFileReader;
use CeusMedia\Common\FS\File\JSON\Writer as JsonFileWriter;
use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Database_Backup extends Logic
{
	protected array $comments				= [];
	protected array $dumps					= [];
	protected string $commentsFile;
	protected string $path;
	protected string $prefixPlaceholder		= '<%?prefix%>';

	/**
	 *	@param		string		$id
	 *	@param		bool		$strict
	 *	@return		object|FALSE
	 */
	public function check( string $id, bool $strict = TRUE )
	{
		if( array_key_exists( $id, $this->dumps ) )
			return $this->dumps[$id];
		if( $strict )
			throw new RuntimeException( 'Invalid dump ID' );
		return FALSE;
	}

	public function dump( string $comment = NULL ): string
	{
		$filename	= "dump_".date( "Y-m-d_H:i:s" ).".sql";
		$pathname	= $this->path.$filename;
		$dbc		= $this->env->getDatabase();
		$dba		= $this->config->getAll( 'module.resource_database.access.', TRUE );
		$prefix		= $dba->get( 'prefix' );
		$tables		= '';																		//  no table selection by default
		if( $prefix ){																			//  prefix has been set
			$tables		= [];																	//  prepare list of tables matching prefix
			foreach( $dbc->query( "SHOW TABLES LIKE '".$prefix."%'" ) as $table )				//  iterate found tables with prefix
				$tables[]	= escapeshellarg( $table[0] );										//  collect table as escaped shell arg
			$tables	= join( ' ', $tables );											//  reduce tables list to tables arg
		}

		$command	= call_user_func_array( "sprintf", [								//  call sprintf with arguments list
			"mysqldump -h%s -P%s -u%s -p%s %s %s > %s",											//  command to replace within
			escapeshellarg( $dba->get( 'host' ) ),											//  configured host name as escaped shell arg
			escapeshellarg( $dba->get( 'port', 3306 ) ),							//  configured port as escaped shell arg
			escapeshellarg( $dba->get( 'username' ) ),										//  configured username as escaped shell arg
			escapeshellarg( $dba->get( 'password' ) ),										//  configured password as escaped shell arg
			escapeshellarg( $dba->get( 'name' ) ),											//  configured database name as escaped shell arg
			$tables,																			//  collected found tables
			escapeshellarg( $pathname ),														//  dump output filename
		] );
		$resultCode		= 0;
		$resultOutput	= [];
		exec( $command, $resultOutput, $resultCode );
		if( $resultCode !== 0 )
			throw new RuntimeException( 'Database dump failed' );

		/*  --  REPLACE PREFIX  --  */
		$regExp		= "@(EXISTS|FROM|INTO|TABLE|TABLES|for table)( `)(".$prefix.")(.+)(`)@U";		//  build regular expression
		$callback	= [$this, '_callbackReplacePrefix'];											//  create replace callback
		rename( $pathname, $pathname."_" );														//  move dump file to source file
		$fpIn		= fopen( $pathname."_", "r" );									//  open source file
		$fpOut		= fopen( $pathname, "a" );												//  prepare empty target file
		while( !feof( $fpIn ) ){																	//  read input file until end
			$line	= fgets( $fpIn );																//  read line buffer
			$line	= preg_replace_callback( $regExp, $callback, $line );							//  perform replace in buffer
			fwrite( $fpOut, $line );																//  write buffer to target file
		}
		fclose( $fpOut );																			//  close target file
		fclose( $fpIn );																			//  close source file
		unlink( $pathname."_" );
		$id		= base64_encode( $filename );
		$this->dumps[$id]	= (object) [
			'id'			=> $id,
			'filename'		=> $filename,
			'pathname'		=> $pathname,
			'filesize'		=> filesize( $pathname ),
			'timestamp'		=> filemtime( $pathname ),
			'comment'		=> $comment,
		];
		if( $comment )
			$this->storeDataInComment( $id, ['comment' => $comment] );
		return $id;
	}

	public function index(): array
	{
		return $this->dumps;
	}

	public function load( string $id, ?string $dbName = NULL, ?string $prefix = NULL ): void
	{
		$dump	= $this->check( $id );
		if( !is_readable( $dump->pathname ) )
			throw new RuntimeException( 'Missing read access to SQL script' );

		$dba		= $this->config->getAll( 'module.resource_database.access.', TRUE );
		$dbName		= $dbName ?: $dba->get( 'name' );
		$prefix		= $prefix ?: $dba->get( 'prefix' );

		$tempName	= $dump->pathname.".tmp";
		$fpIn		= fopen( $dump->pathname, "r" );								//  open source file
		$fpOut		= fopen( $tempName, "a" );									//  prepare empty target file
		while( !feof( $fpIn ) ){														//  read input file until end
			$line	= fgets( $fpIn );													//  read line buffer
			$line	= str_replace( "<%?prefix%>", $prefix, $line );				//  replace table prefix placeholder
			fwrite( $fpOut, $line );													//  write buffer to target file
		}
		fclose( $fpOut );																//  close target file
		fclose( $fpIn );																//  close source file
		$command	= call_user_func_array( "sprintf", [						//  call sprintf with arguments list
			"mysql -h%s -P%s -u%s -p%s %s < %s",										//  command to replace within
			escapeshellarg( $dba->get( 'host' ) ),									//  configured host as escaped shell arg
			escapeshellarg( $dba->get( 'port', 3306 ) ),					//  configured port as escaped shell arg
			escapeshellarg( $dba->get( 'username' ) ),								//  configured username as escaped shell arg
			escapeshellarg( $dba->get( 'password' ) ),								//  configured password as escaped shell arg
			escapeshellarg( $dbName ),													//  configured database name as escaped shell arg
			escapeshellarg( $tempName ),												//  temp file name as escaped shell arg
		] );
		exec( $command );
		unlink( $tempName );
	}

	public function remove( string $id ): void
	{
		$dump	= $this->check( $id );
		@unlink( $dump->pathname );
		if( array_key_exists( $id, $this->comments ) ){
			unset( $this->comments[$id] );
			JsonFileWriter::save( $this->commentsFile, $this->comments );
		}
	}

	public function storeDataInComment( $id, $data )
	{
		$dump	= $this->check( $id );
		if( !array_key_exists( $id, $this->comments ) )
			$this->comments[$id]	= ['comment' => ''];
		if( is_string( $this->comments[$id] ) )
			$this->comments[$id]	= ['comment' => $dump->comment];
		foreach( $data as $key => $value ){
			if( is_null( $value ) && isset( $this->comments[$id][$key] ) )
				unset( $this->comments[$id][$key] );
			else
				$this->comments[$id][$key]	= $value;
		}
		FileWriter::save( $this->commentsFile, json_encode( $this->comments, JSON_PRETTY_PRINT ) );
	}

	//  --  PROTECTED METHODS  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$moduleConfig	= $this->config->getAll( 'module.admin_database_backup.', TRUE );
		$basePath		= $this->env->uri;
		if( $this->env->hasModule( 'Resource_Frontend' ) ){
			$frontend	= $this->env->getLogic()->get( 'Frontend' );
			$basePath	= $frontend->getPath();
		}
		$this->path			= $basePath.$moduleConfig->get( 'path' );
		$this->commentsFile	= $this->path.'comments.json';
		if( !file_exists( $this->path ) )
			FolderEditor::createFolder( $this->path );
		if( !file_exists( $this->commentsFile ) )
			file_put_contents( $this->commentsFile, '[]' );
		$this->comments	= JsonFileReader::load( $this->commentsFile, TRUE );
		$this->dumps	= $this->readIndex();
	}

	protected function _callbackReplacePrefix( array $matches ): string
	{
		if( $matches[1] === 'for table' )
			return $matches[1].$matches[2].$matches[4].$matches[5];
		return $matches[1].$matches[2].$this->prefixPlaceholder.$matches[4].$matches[5];
	}

	protected function readIndex(): array
	{
		$list	= [];
		$map	= [];
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

			$comment	= NULL;
			if( array_key_exists( $id, $this->comments ) )
				$comment	= $this->comments[$id];

			$list[$timestamp.uniqid()]	= (object) [
				'id'			=> $id,
				'filename'		=> $entry->getFilename(),
				'pathname'		=> $entry->getPathname(),
				'filesize'		=> filesize( $entry->getPathname() ),
				'timestamp'		=> $timestamp,
				'comment'		=> $comment,
			];
		}
		krsort( $list );
		foreach( $list as $item )
			$map[$item->id]	= $item;
		return $map;
	}
}
