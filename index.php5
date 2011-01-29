<?php
require_once 'cmClasses/0.7.2/autoload.php5';

class Modules{

	protected $request;
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
		$accepts	= array( 'text/html' => 1 );
		if( $this->request->hasHeader( 'accept' ) )
			$accepts	= array_pop( $this->request->getHeadersByName( 'accept' ) )->getValue( TRUE );
		foreach( array_keys( $accepts ) as $accept ){
			switch( $accept ){
				case 'text/html':
					$this->response->setBody( $this->buildHTML( $this->getModuleList() ) );
					return TRUE;
				case 'application/json':
				default:
					$this->response->setBody( $this->buildJSON( $this->getModuleList() ) );
//					$this->response->addHeaderPair( 'Content-type', $accept );
					return TRUE;
			}
		}
		return FALSE;
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

	protected function getModuleList(){
		$list	= array();
		$index	= new File_RecursiveNameFilter( './', 'module.xml' );
		foreach( $index as $entry ){
			$id		= preg_replace( '@^./@', '', $entry->getPath() );
			$id		= str_replace( '/', '_', $id );
			try{
				$xml	= @XML_ElementReader::readFile( $entry->getPathname() );
				$obj	= new stdClass();
				$obj->title				= (string) $xml->title;
				$obj->description		= (string) $xml->description;
				$obj->version			= (string) $xml->version;
			}
			catch( Exception $e ){
				$obj	= new stdClass();
				$obj->title				= $id;
				$obj->description		= 'XML file is broken';
				$obj->version			= '';
			}
			$list[$id]	= $obj;
		}
		ksort( $list );
		return $list;
	}
}
new Modules();
?>
