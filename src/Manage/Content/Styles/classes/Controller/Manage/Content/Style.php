<?php

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Content_Style extends Controller
{
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Logic_Frontend $frontend;
	protected string $basePath;
	protected string $pathCss;
	protected string $theme;
	protected array $files		= [];

	public function index( ?string $file = NULL ): void
	{
		if( strlen( trim( $file ) ) ){
			if( !file_exists( $this->pathCss.$file ) ){
				$this->messenger->noteError( 'Invalid file' );
				$this->restart( NULL, TRUE );
			}
			if( $this->request->has( 'save' ) ){
				$content	= $this->request->get( 'content' );
				FileWriter::save( $this->pathCss.$file, $content );
				$this->restart( $file, TRUE );
			}
			$this->addData( 'content', FileReader::load( $this->pathCss.$file ) );
			$this->addData( 'file', $file );
			$this->addData( 'readonly', !is_writable( $this->pathCss.$file ) );
		}
		else{
			$this->addData( 'file', FALSE );
		}
	}

	public function ajaxSaveContent(): void
	{
		$file		= $this->request->get( 'file' );
		$content	= $this->request->get( 'content' );
		$status		= 500;
		$result		= [
			'status'	=> 'error',
			'data'		=> 'File not existing'
		];
		if( file_exists( $this->pathCss.$file ) ){
			try{
				$result	= FileWriter::save( $this->pathCss.$file, $content );
				$status	= 200;
				$result	= [
					'status'	=> 'data',
					'data'		=> $result
				];
			}
			catch( Exception $e ){
				$result		= [
					'status'	=> 'error',
					'data'		=> $e->getMessage()
				];
			}
		}
		$response	= $this->env->getResponse();
		$response->setStatus( $status );
		$response->addHeaderPair( 'Content-Type', 'application/json' );
		$response->setBody( json_encode( $result ) );
		$response->send();
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
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
}
