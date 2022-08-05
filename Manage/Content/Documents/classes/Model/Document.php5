<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */

use CeusMedia\HydrogenFramework\Environment;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
class Model_Document
{
	protected $path;

	public function __construct( Environment $env, $path )
	{
		$this->path	= $path;
	}

	public function add( $upload )
	{
		if( !is_array( $upload ) )
			throw new InvalidArgumentException( 'No valid upload array given' );
		if( $upload['error'] ){
			$handler	= new Net_HTTP_UploadErrorHandler();
			$handler->handleErrorFromUpload( $upload );
		}
		if( substr( $upload['name'], 0, 1 ) === '.' )
			throw new RuntimeException( 'File names starting with a dot are permitted.' );
		if( !@move_uploaded_file( $upload['tmp_name'], $this->path.$upload['name'] ) )
			throw new RuntimeException( 'Error during file upload' );
		return TRUE;
	}

	public function count()
	{
		return count( $this->index() );
	}

	public function index( $limit = 0, $offset = 0 )
	{
		$index	= new DirectoryIterator( $this->path );
		$list	= [];
		foreach( $index as $entry ){
			if( $entry->isDir() || $entry->isDot() )
				continue;
			if( substr( $entry->getFilename(), 0, 1 ) === '.'  )
				continue;
			$list[]	= $entry->getFilename();
		}
		natcasesort( $list );
		if( $limit )
			$list	= array_splice( $list, $offset, $limit );
		return $list;
	}

	public function remove( $fileName )
	{
		if( substr( $fileName, 0, 1 ) !== '.'  )
			if( @unlink( $this->path.$fileName ) )
				return TRUE;
		return FALSE;
	}
}
