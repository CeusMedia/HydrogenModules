<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\Net\HTTP\UploadErrorHandler;
use CeusMedia\HydrogenFramework\Environment;


/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Document
{
	protected Environment $env;
	protected string $path;

	public function __construct( Environment $env, string $path )
	{
		$this->env	= $env;
		$this->path	= $path;
	}

	public function add( array $upload ): bool
	{
		if( $upload['error'] ){
			$handler	= new UploadErrorHandler();
			$handler->handleErrorFromUpload( $upload );
		}
		if( str_starts_with( $upload['name'], '.' ) )
			throw new RuntimeException( 'File names starting with a dot are permitted.' );
		if( !@move_uploaded_file( $upload['tmp_name'], $this->path.$upload['name'] ) )
			throw new RuntimeException( 'Error during file upload' );
		return TRUE;
	}

	public function count(): int
	{
		return count( $this->index() );
	}

	public function index( $limit = 0, $offset = 0 ): array
	{
		$index	= new DirectoryIterator( $this->path );
		$list	= [];
		foreach( $index as $entry ){
			if( $entry->isDir() || $entry->isDot() )
				continue;
			if( str_starts_with( $entry->getFilename(), '.' ) )
				continue;
			$list[]	= $entry->getFilename();
		}
		natcasesort( $list );
		if( $limit )
			$list	= array_splice( $list, $offset, $limit );
		return $list;
	}

	public function remove( $fileName ): bool
	{
		if( !str_starts_with( $fileName, '.' ) )
			if( @unlink( $this->path.$fileName ) )
				return TRUE;
		return FALSE;
	}
}
