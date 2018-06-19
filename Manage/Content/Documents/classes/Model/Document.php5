<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
/**
 *	...
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
class Model_Document {

	protected $path;

	public function __construct( CMF_Hydrogen_Environment $env, $path ){
		$this->path	= $path;
	}

	public function add( $upload ){
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

	public function index(){
		$index	= new DirectoryIterator( $this->path );
		$list	= array();
		foreach( $index as $entry ){
			if( $entry->isDir() || $entry->isDot() )
				continue;
			if( substr( $entry->getFilename(), 0, 1 ) === '.'  )
				continue;
			$list[]	= $entry->getFilename();
		}
		natcasesort( $list );
		return $list;
	}

	public function remove( $fileName ){
		if( substr( $fileName, 0, 1 ) !== '.'  )
			if( @unlink( $this->path.$fileName ) )
				return TRUE;
		return FALSE;
	}
}
?>
