<?php

use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\Common\Net\HTTP\Status as HttpStatus;
use CeusMedia\Common\XML\DOM\Builder as XmlBuilder;
use CeusMedia\Common\XML\DOM\Node as XmlNode;
use CeusMedia\HydrogenFramework\View;

class View_File extends View{

	public function index(){
		$file	= $this->getData( 'file' );
		$path	= $this->getData( 'sourcePath' );

		$mimeTypes		= ['*'.'/'.'*' => 1];												//  default if no Accept header is set
		if( $acceptField = $this->env->getRequest()->getHeader( 'Accept', FALSE ) )					//  Accept header for content negotiation found
			$mimeTypes	= $acceptField->decodeQualifiedValues( $acceptField->getValue() );			//  decode qualified MIME types

		if( !$file )
			$this->negotiateContentOnMiss( array_keys( $mimeTypes ) );
		$this->negotiateContentOnHit( $path, $file, array_keys( $mimeTypes ) );
	}

	protected function negotiateContentOnHit( $path, $file, $mimeTypes ){
		$this->env->getConfig()->get( 'path.contents' );
		$sourceFilePath	= $path.$file->hash;
		if( !file_exists( $sourceFilePath ) )
			throw new RuntimeException( 'Given source file is not existing' );
		if( !is_readable( $sourceFilePath ) )
			throw new RuntimeException( 'Given source file is not readable' );

		if( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ){											//  atleast PHP 5.3
			header_remove( 'Cache-Control' );														//  remove sent cache control header
			header_remove( 'Pragma' );																//  remove sent pragma header
		}
		else{
			header( 'Cache-Control: public' );
			header( 'Pragma: cache' );
		}

		if( $headerSince = $this->env->getRequest()->getHeader( 'If-Modified-Since', FALSE ) ){
			if( strtotime( $headerSince->getValue() ) === $file->modifiedAt ){
				HttpStatus::sendHeader( 304 );
				exit;
			}
		}
		while( ob_get_level() > 1 ) ob_end_clean();

		if( $this->getData( 'download', FALSE ) ){
			HttpDownload::sendFile( $sourceFilePath, $file->fileName, TRUE );
		}
		header( 'Content-Type: '.$file->mimeType );
		header( 'Content-Length: '.$file->fileSize );
		header( 'Cache-Control: max-age=2592000, public' );
		header( 'Last-Modified: '.date( 'r', $file->modifiedAt ) );
		header( 'Expires: '.date( 'r', time() + 2592000 ) );
		$handle	= fopen( $sourceFilePath, 'rb' );
		while( !feof( $handle ) )
			print( fread( $handle, 1024 ) );
		exit;
	}

	protected function negotiateContentOnMiss( $mimeTypes ){
		$response	= $this->env->getResponse();
		$response->setStatus( 404, TRUE );

		foreach( $mimeTypes as $mimeType ){
			switch( $mimeType ){
				case 'application/xhtml+xml':
				case 'text/html':
					$content	= '<h1>Error 404: Not found</h1><p>The requested resource is not available.</p>';
					$response->setBody( $content );
					$response->send( NULL, TRUE, TRUE );
				case 'text/plain':
				case '*/*':
					$response->setBody( 'Error 404: Not found' );
					$response->send( NULL, TRUE, TRUE );
				case 'application/xml':
					$node	= new XmlNode( 'response', 'Not found', array(
						'type'	=> 'error',
						'code'	=> 404,
					) );
					$response->setBody( XmlBuilder::build( $node ) );
					$response->send( NULL, TRUE, TRUE );
				case 'application/json':
				case 'text/json':
					$data	= array(
						'status'	=> 'error',
						'error'		=> 'Not found',
						'code'		=> 404,
					);
					$response->setBody( json_encode( $data ) );
					$response->send( NULL, TRUE, TRUE );
			}
		}
		$response->send( NULL, TRUE, TRUE );
	}
}
?>
