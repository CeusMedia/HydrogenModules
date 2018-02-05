<?php
class Controller_File extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->logic		= new Logic_FileBucket( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_file.', TRUE );

		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'sourcePath', $this->logic->getPath() );
	}

	public function clean(){
		$index	= new DirectoryIterator( $this->logic->getPath() );
		foreach( $index as $entry ){
			if( $entry->isDir() || $entry->isDot() )
				continue;
			if( !preg_match( '/^[a-z0-9]{32}$/', $entry->getFilename() ) )
				continue;
			if( !$this->logic->getByHash( $entry->getFilename() ) ){
				unlink( $entry->getPathname() );
			}
		}
		$this->restart( NULL );
	}

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $args4 = NULL, $arg5 = NULL, $arg6 = NULL, $arg7 = NULL, $arg8 = NULL ){
		$uriPath	= implode( "/", func_get_args() );
		$file		= $this->logic->getByPath( $uriPath );
		$this->addData( 'uriPath', $uriPath );
		$this->addData( 'file', $file );
		if( $file )
			$this->logic->noteView( $file->fileId );
	}
}
?>
