<?php
require_once 'cmClasses/trunk/autoload.php5';
require_once 'cmFrameworks/trunk/autoload.php5';

class Modules{

	/**	@var	Net_HTTP_Request_Receiver	$request		HTTP request object */
	protected $request;
	/**	@var	Net_HTTP_Response			$response		HTTP response object */
	protected $response;

	public function __construct(){
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

	protected function dispatch(){
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

	protected function dispatchJson(){
		switch( $this->request->get( 'do' ) ){
			case 'list':
				return json_encode( $this->getModuleList( TRUE ) );
			case 'index':
			default:
				return json_encode( $this->getModuleList() );
		}
	}

	protected function buildHTML( $modules ){
		$list		= array();
		foreach( $modules as $moduleName => $moduleData ){
			$label	= $moduleData->title." ".$moduleData->version;
			if( !empty( $moduleData->description ) )
				$label	= UI_HTML_Elements::Acronym( $label, $moduleData->description );
			$list[]	= UI_HTML_Elements::ListItem( $label );
		}
		$list		= UI_HTML_Elements::unorderedList( $list );
		$page		= new UI_HTML_PageFrame();
		$page->addStylesheet( 'http://css.ceusmedia.com/blueprint/reset.css' );
		$page->addStylesheet( 'http://css.ceusmedia.com/blueprint/typography.css' );
		$page->addStylesheet( 'html.css' );
		$page->addBody( '<h1>Modules for <a href="#">Hydrogen</a></h1>'.$list );
		return $page->build();
	}

	protected function buildJSON( $modules ){
		return json_encode( $modules );
	}

	protected function getModuleList( $full = NULL ){
		$list	= array();
		$index	= new File_RecursiveNameFilter( './', 'module.xml' );
		foreach( $index as $entry ){
			$id		= preg_replace( '@^./@', '', $entry->getPath() );
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
new Modules();
?>
