<?php
use CeusMedia\Common\FS\File\RecursiveNameFilter as RecursiveFileFinder;
use CeusMedia\Common\Net\HTTP\Header\Field as HttpHeaderField;
use CeusMedia\Common\Net\HTTP\Request\Receiver as HttpRequestReceiver;
use CeusMedia\Common\Net\HTTP\Response as HttpResponse;
use CeusMedia\Common\Net\HTTP\Response\Sender as HttpResponseSender;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\Common\UI\HTML\PageFrame as HtmlPage;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Reader;

error_reporting( E_ALL );
ini_set( 'display_errors', 'On' );

( include_once __DIR__.'/vendor/autoload.php' ) or die( 'Install packages using composer, first!' );

new Modules();

class Modules
{
	/**	@var	HttpRequestReceiver		$request		HTTP request object */
	protected HttpRequestReceiver $request;

	/**	@var	HttpResponse			$response		HTTP response object */
	protected HttpResponse $response;

	public function __construct()
	{
		error_reporting( E_ALL );
		$this->request	= new HttpRequestReceiver();
		$this->response	= new HttpResponse();
		try{
			if( !$this->dispatch() )
				throw new InvalidArgumentException( 'No valid content type requested' );
			$sender	= new HttpResponseSender( $this->response );
			$sender->send();
		}
		catch( Exception $e ){
			die( HtmlExceptionPage::render( $e ) );
		}
	}

	protected function dispatch(): bool
	{
		$accepts	= array( new HttpHeaderField( 'accept', 'text/html;q=1' ) );
		if( $this->request->has( 'json' ) )
			$accepts	= array( new HttpHeaderField( 'accept', 'application/json' ) );
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
				$label	= HtmlElements::Acronym( $label, htmlentities( $moduleData->description, ENT_QUOTES, 'UTF-8' ) );
			$list[]	= HtmlElements::ListItem( $label );
		}
		$list		= HtmlElements::unorderedList( $list );
		$page		= new HtmlPage();
		$page->addStylesheet( 'https://cdn.ceusmedia.de/css/bootstrap.min.css' );
		$page->addStylesheet( 'html.css' );
		$page->addBody( '<div class="container"><div class="hero-unit"><h2>Hydrogen Modules</h2>Collection of open modules for <a href="https://github.com/CeusMedia/HydrogenFramework">Hydrogen Framework</a></h2></div>'.$list.'</div>' );
		return $page->build();
	}

	protected function getModuleList( bool $full = FALSE ): array
	{
		$list	= [];
		$index	= new RecursiveFileFinder( './src/', 'module.xml' );
		foreach( $index as $entry ){
			$id		= preg_replace( '@^./src/@', '', $entry->getPath() );
			if( !preg_match( '@^[A-Z]@', $id ) )
				continue;
			$id		= str_replace( '/', '_', $id );
			try{
				$module	= Reader::load( $entry->getPathname(), $id );
				if( !$full )
					$module	= (object) [
						'title'			=> $module->title,
						'description'	=> $module->description,
						'version'		=> $module->version,
					];
				$list[$id]	= $module;
			}
			catch( Exception $e ){
			}
		}
		ksort( $list );
		return $list;
	}
}
