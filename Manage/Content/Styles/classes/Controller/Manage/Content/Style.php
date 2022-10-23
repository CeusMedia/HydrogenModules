<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Content_Style extends Controller{

	protected $request;
	protected $messenger;
	protected $frontend;
	protected $basePath;
	protected $pathCss;
	protected $theme;
	protected $files		= [];

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();

		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->basePath		= $this->frontend->getPath( 'themes' );
		$this->theme		= $this->frontend->getConfigValue( 'layout.theme' );
		$this->pathCss		= $this->basePath.$this->theme.'/css/';

		if( file_exists( $this->pathCss ) ){
			$index	= new DirectoryIterator( $this->pathCss );
			foreach( $index as $file ){
				if( $file->isDir() || $file->isDot() )
					continue;
				$this->files[]	= $file->getFilename();
			}
		}
		natcasesort( $this->files );
		$this->addData( 'files', $this->files );
	}

	public function index( $file = NULL ){
		if( strlen( trim( $file ) ) ){
			if( !file_exists( $this->pathCss.$file ) ){
				$this->messenger->noteError( 'Invalid file' );
				$this->restart( NULL, TRUE );
			}
			if( $this->request->has( 'save' ) ){
				$content	= $this->request->get( 'content' );
				File_Writer::save( $this->pathCss.$file, $content );
				$this->restart( $file, TRUE );
			}
			$this->addData( 'content', File_Reader::load( $this->pathCss.$file ) );
			$this->addData( 'file', $file );
			$this->addData( 'readonly', !is_writable( $this->pathCss.$file ) );
		}
		else{
			$this->addData( 'file', FALSE );
		}
	}

	public function ajaxSaveContent(){
		$file		= $this->request->get( 'file' );
		$content	= $this->request->get( 'content' );
		$status		= 500;
		$result		= array(
			'status'	=> 'error',
			'data'		=> 'File not existing'
		);
		if( file_exists( $this->pathCss.$file ) ){
			try{
				$result	= File_Writer::save( $this->pathCss.$file, $content );
				$status	= 200;
				$result	= array(
					'status'	=> 'data',
					'data'		=> $result
				);
			}
			catch( Exception $e ){
				$result		= array(
					'status'	=> 'error',
					'data'		=> $e->getMessage()
				);
			}
		}
		$response	= $this->env->getResponse();
		$response->setStatus( $status );
		$response->addHeaderPair( 'Content-Type', 'application/json' );
		$response->setBody( json_encode( $result ) );
		$response->send();
	}
}
