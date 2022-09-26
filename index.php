<?php
use CeusMedia\Common\FS\File\RecursiveNameFilter as RecursiveFileFinder;

( include_once __DIR__.'/vendor/autoload.php' ) or die( 'Install packages using composer, first!' );

error_reporting( E_ALL );
ini_set( 'display_errors', 'On' );
new Modules();

class Modules
{
	/**	@var	Net_HTTP_Request_Receiver	$request		HTTP request object */
	protected $request;

	/**	@var	Net_HTTP_Response			$response		HTTP response object */
	protected $response;

	public function __construct()
	{
		error_reporting( E_ALL );
		$this->request	= new Net_HTTP_Request_Receiver();
		$this->response	= new Net_HTTP_Response();
		try{
			if( !$this->dispatch() )
				throw new InvalidArgumentException( 'No valid content type requested' );
			$sender	= new Net_HTTP_Response_Sender( $this->response );
			$sender->send();
		}
		catch( Exception $e ){
			die( UI_HTML_Exception_Page::render( $e ) );
		}
	}

	protected function dispatch(): bool
	{
		$accepts	= array( new Net_HTTP_Header_Field( 'accept', 'text/html;q=1' ) );
		if( $this->request->has( 'json' ) )
			$accepts	= array( new Net_HTTP_Header_Field( 'accept', 'application/json' ) );
		else if( $this->request->hasHeader( 'accept' ) )
			$accepts	= $this->request->getHeadersByName( 'accept' );

		foreach( $accepts as $accept ){
			$mimeTypes	= $accept->decodeQualifiedValues( $accept->getValue() );
			foreach( $mimeTypes as $mimeType => $quality ){
				switch( $mimeType ){
					case 'application/json':
						$this->response->addHeaderPair( 'Content-type', 'application/json' );
						$this->response->setBody( $this->dispatchJson() );
	//					$this->response->addHeaderPair( 'Content-type', $accept );
						return TRUE;
					case 'text/html':
						$this->response->setBody( $this->buildHTML( $this->getModuleList() ) );
						return TRUE;
				}
			}
		}
		$this->response->setBody( $this->buildHTML( $this->getModuleList() ) );
		return FALSE;
	}

	protected function dispatchJson(): string
	{
		switch( $this->request->get( 'do' ) ){
			case 'list':
				return json_encode( $this->getModuleList( TRUE ) );
			case 'index':
			default:
				return json_encode( $this->getModuleList() );
		}
	}

	protected function buildHTML( array $modules ): string
	{
		$list		= [];
		foreach( $modules as $moduleName => $moduleData ){
			$label	= $moduleData->title." ".$moduleData->version;
			if( !empty( $moduleData->description ) )
				$label	= UI_HTML_Elements::Acronym( $label, htmlentities( $moduleData->description, ENT_QUOTES, 'UTF-8' ) );
			$list[]	= UI_HTML_Elements::ListItem( $label );
		}
		$list		= UI_HTML_Elements::unorderedList( $list );
		$page		= new UI_HTML_PageFrame();
		$page->addStylesheet( 'https://cdn.ceusmedia.de/css/bootstrap.min.css' );
		$page->addStylesheet( 'html.css' );
		$page->addBody( '<div class="container"><div class="hero-unit"><h2>Hydrogen Modules</h2>Collection of open modules for <a href="https://github.com/CeusMedia/HydrogenFramework">Hydrogen Framework</a></h2></div>'.$list.'</div>' );
		return $page->build();
	}

	protected function getModuleList( bool $full = FALSE ): array
	{
		$list	= [];
		$index	= new RecursiveFileFinder( './', 'module.xml' );
		foreach( $index as $entry ){
			$id		= preg_replace( '@^./@', '', $entry->getPath() );
			if( !preg_match( '@^[A-Z]@', $id ) )
				continue;
			$id		= str_replace( '/', '_', $id );
			try{
				$module	= CMF_Hydrogen_Environment_Resource_Module_Reader::load( $entry->getPathname(), $id );
				if( !$full )
					$module	= (object) array(
						'title'			=> $module->title,
						'description'	=> $module->description,
						'version'		=> $module->version,
					);
				$list[$id]	= $module;
			}
			catch( Exception $e ){
			}
		}
		ksort( $list );
		return $list;
	}
}
