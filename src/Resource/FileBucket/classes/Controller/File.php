<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_File extends Controller
{
	protected Logic_FileBucket $logic;

	public function clean()
	{
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
		$this->restart();
	}

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $args4 = NULL, $arg5 = NULL, $arg6 = NULL, $arg7 = NULL, $arg8 = NULL )
	{
		$uriPath	= implode( "/", func_get_args() );
		$file		= $this->logic->getByPath( $uriPath );
		$this->addData( 'uriPath', $uriPath );
		$this->addData( 'file', $file );
		if( $file ){
			if( $this->env->getRequest()->has( 'download' ) ){
				$this->addData( 'download', $this->env->getRequest()->has( 'download' ) );
//				@todo implement: add column to model
//				$this->logic->noteDownload( $file->fileId );
			}
			else{
				$this->logic->noteView( $file->fileId );
			}
		}
	}

	protected function __onInit(): void
	{
		$this->logic		= new Logic_FileBucket( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_file.', TRUE );

		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'sourcePath', $this->logic->getPath() );
	}
}
