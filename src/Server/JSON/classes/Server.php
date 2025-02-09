<?php
/**
 *	Chat server.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\Net\HTTP\Sniffer\MimeType as MimeTypeSniffer;
use CeusMedia\Common\UI\HTML\PageFrame as HtmlPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\UI\OutputBuffer;
use CeusMedia\HydrogenFramework\Application\Web\Site as WebSite;

/**
 *	Chat server.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class Server extends WebSite
{
	protected function logRequestInDatabase( $responseTime, $responseLength )
	{
		//  write to Model_Server_Request
	}

	protected function logRequestInFile( $responseTime, $responseLength )
	{
		$message	= sprintf(
			"%s %s %s %s %s %s\n",
			time(),																//  current timestamp
			getEnv( 'REMOTE_ADDR' ),											//  client IP
			getEnv( 'REQUEST_METHOD' ),											//  GET or POST
			$responseLength,													//  in bytes
			$responseTime,														//  in microseconds
			getEnv( 'REDIRECT_URL' )											//  requested URL
		);
		$writer	= new FileWriter( 'logs/request.log' );
		$writer->appendString( $message );
	}

	protected function logOnComplete(): void
	{
		$responseLength	= $this->env->getResponse()->getLength();
		$responseTime	= $this->env->getClock()->stop( 6, 0 );

		if( $this->env->getRequest()->get( 'ttl' ) )
			$responseTime	-= $this->env->getRequest()->get( 'ttl' ) * 1000;

		$this->logRequestInFile( $responseTime, $responseLength );
#		$this->logRequestInDatabase( $responseTime, $responseLength );
	}

	protected function main( $defaultController = 'index', $defaultAction = 'index' ): string
	{
		$config		= $this->env->getConfig();
		$request	= $this->env->getRequest();
		$response	= $this->env->getResponse();

		$data		= NULL;
		$debug		= NULL;
		$exception	= NULL;

		error_log( getEnv( 'HTTP_REFERER' )."\n", 3, 'logs/referer.log' );
		if( !$this->validateReferer() )
			$this->throw403();

		#if( getEnv( 'HTTP_REFERER' ) )

		$buffer		= new OutputBuffer( TRUE );
		$dispatcher	= new Dispatcher( $this->env );
		try {
			$data	= $dispatcher->dispatch();
		} catch( Exception $e ) {
			$exception	= $e->getMessage();
			$data	= -105;
			if( $e instanceof RuntimeException && $e->getCode() > 200 && $e->getCode() < 300 ) {
				if( $e->getCode() == 220 )
#					$this->throw401();
					$response->setStatus( '401 Unauthorized' );
				else
#					$this->throw404();
					$response->setStatus( '404 Not Found' );
			}
			else {
#				$this->throw500();
				$response->setStatus( '500 Internal Server Error' );
			}
		}
		$debug	= $buffer->get( TRUE );

		$data	= array(																			//  prepare return data
			'appName'		=> $config->get( 'app.name' ),
			'appVersion'	=> $config->get( 'app.version' ),
			'time'			=> microtime( TRUE ),
			'data'			=> $data																//  ...append message list
		);
		if( $debug )
			$data['debug']	= $debug;
		if( $exception ) {
			$data['exception']	= $exception;
			try{
				if( $config->get( 'module.server_json.exception.serialize' ) )
					$data['serial']		= @serialize( $exception );
			}
			catch( PDOException $e ){}
		}

	//	$data['requestHeaders']	= $request->headers->toArray();
//		$this->messenger->noteNotice( print_m( \CeusMedia\Common\Net\HTTP\Header\Field::decodeQualifiedValues( getEnv( 'HTTP_ACCEPT' ) ), NULL, NULL, TRUE ) );

		$mimeTypesAllowed	= [
			'application/json',
			'text/json',
			'text/javascript',
			'text/plain',
			'text/html'
		];
		$mimeTypeDefault	= 'application/json;charset=utf8';
		$mimeTypeSniffer	= new MimeTypeSniffer;
		$mimeType			= $mimeTypeSniffer->getMimeType( $mimeTypesAllowed, $mimeTypeDefault );
		$response->addHeaderPair( 'Content-type', $mimeType );
		$allowed			= $config->get( 'module.server_json.access.allow.origin' );
		if( $allowed )
			$response->addHeaderPair( 'Access-Control-Allow-Origin', $allowed );
		return json_encode( $data );
	}

	protected function negotiateContentType()
	{
		$supported	= ['text/json', 'text/html'];
		$request	= $this->env->getRequest();

//		var_dump( $request->getHeaders() );
		$accepts	= $request->getHeadersByName( 'accept' );
		foreach( $accepts as $accept ){
			$types	= array_keys( $accept->getValue( true ) );
			foreach( $types as $type )
				if( in_array( $type, $supported ) )
					return $type;
		}
		return $supported[0];
	}

	protected function respond( string $body, array $headers = [] ): object
	{
		$config		= $this->env->getConfig();
		$request	= $this->env->getRequest();
		$response	= $this->env->getResponse();

		switch( $this->negotiateContentType() ){
			case 'text/html':
				if( $this->env->getModules()->has( 'Server_JSON_Browser' ) ){
					if( $config->get( 'module.server_json_browser.enabled' ) ){
						$channel	= new Browser( $this->env );
						$body		= $channel->render( $body, $headers );
						return parent::respond( $body, $headers );
					}
				}
				break;
			case 'text/json':
				return parent::respond( $body, $headers );
		}
		return parent::respond( $body, $headers );
	}

	protected function throw401()
	{
		$this->env->getResponse()->setStatus( '401 Unauthorized' );
		$heading	= HtmlTag::create( 'h1', '401 Unauthorized' );
		$paragraph	= HtmlTag::create( 'p', 'You need to send a token, which you get by posting the shared secret to /auth/getToken.' );
		$page		= new HtmlPage();
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/reset.css' );
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/typography.css' );
		$page->addBody( $heading.$paragraph );
		$page	= $page->build( ['style' => 'margin: 2em'] );
		$this->respond( $page );
		$this->logOnComplete();
		$this->env->close();
		exit( 1 );
	}

	protected function throw403()
	{
		$this->env->getResponse()->setStatus( '403 Forbidden' );
		$heading	= HtmlTag::create( 'h1', '403 Forbidden' );
		$paragraph	= HtmlTag::create( 'p', 'This service can not be accessed by your IP address.' );
		$page		= new HtmlPage();
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/reset.css' );
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/typography.css' );
		$page->addBody( $heading.$paragraph );
		$page	= $page->build( ['style' => 'margin: 2em'] );
		$this->respond( $page );
		$this->logOnComplete();
		$this->env->close();
		exit( 1 );
	}

	protected function throw404()
	{
		$this->env->getResponse()->setStatus( '404 Not Found' );
		$heading	= HtmlTag::create( 'h1', '404 Not Found' );
		$paragraph	= HtmlTag::create( 'p', 'The resource you have requested is not existing on this server.' );
		$page		= new HtmlPage();
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/reset.css' );
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/typography.css' );
		$page->addBody( $heading.$paragraph );
		$page	= $page->build( ['style' => 'margin: 2em'] );
		$this->respond( $page );
		$this->logOnComplete();
		$this->env->close();
		exit( 1 );
	}
/*
	protected function throw500( $reason )
	{
		$this->env->getResponse()->setStatus( '500 Internal Server Error' );
		$heading	= HtmlTag::create( 'h1', '500 Internal Server Error' );
		$paragraph	= HtmlTag::create( 'p', 'An exception occurred while executing your request. Please restart the service.' );
		$page	= new HtmlPage();
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/reset.css' );
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/typography.css' );
		$page->addBody( $heading.$paragraph );
		$page	= $page->build( ['style' => 'margin: 2em'] );
		$this->respond( $page );
		$this->logOnComplete();
		$this->env->close();
		exit( 1 );
	}*/

	protected function validateReferer()
	{
		$refererAllowed	= trim( $this->env->config->get( 'module.server_json.referers.only' ) );	//  get allowed referrers from config
		if( !$refererAllowed )																		//  no referrers defined
			return TRUE;																			//  so everyone can access

		$refererActual	= parse_url( getEnv( 'HTTP_REFERER' ) );									//  get actually requesting referer
		if( empty( $refererActual['host'] )	)														//  no valid referer transmitted
			return FALSE;																			//  so block access

		$referers	= preg_split( '/\s,\s/', $refererAllowed );										//  if multiple referrers are defined
		if( in_array( $refererActual['host'], $referers ) )											//  match allowed referer host with requesting referer host
			return TRUE;																			//  allowed
		return FALSE;																				//  otherwise block
	}
}
