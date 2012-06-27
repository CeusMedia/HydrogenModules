<?php
/**
 *	Chat server.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Server.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
/**
 *	Chat client.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@extends		CMF_Hydrogen_Application
 *	@uses			Alg_Object_Factory
 *	@uses			Alg_Object_MethodFactory
 *	@uses			UI_HTML_PageFrame
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Server.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
class Server extends CMF_Hydrogen_Application_Web_Site {

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
		$writer	= new File_Writer( 'logs/request.log' );
		$writer->appendString( $message );
	}

	protected function logOnComplete()
	{
		$responseLength	= $this->env->getResponse()->getLength();
		$responseTime	= $this->env->getClock()->stop( 6, 0 );

		if( $this->env->getRequest()->get( 'ttl' ) )
			$responseTime	-= $this->env->getRequest()->get( 'ttl' ) * 1000;

		$this->logRequestInFile( $responseTime, $responseLength );
#		$this->logRequestInDatabase( $responseTime, $responseLength );
	}

	protected function main( $defaultController = 'index', $defaultAction = 'index' )
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

		$buffer		= new UI_OutputBuffer( TRUE );
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
			'data'			=> $data																//  ...append message list
		);
		if( $debug )
			$data['debug']	= $debug;
		if( $exception ) {
			$data['exception']	= $exception;
			try{
				$data['serial']		= @serialize( $e );
			}
			catch( PDOException $e ){}
		}

	//	$data['requestHeaders']	= $request->headers->toArray();

		$mimeTypesAllowed	= array(
			'application/json',
			'text/json',
			'text/javascript',
			'text/plain',
			'text/html'
		);
		$mimeTypeDefault	= 'application/json;charset=utf8';
		$mimeTypeSniffer	= new Net_HTTP_Sniffer_MimeType;
		$mimeType			= $mimeTypeSniffer->getMimeType( $mimeTypesAllowed, $mimeTypeDefault );
		$response->addHeaderPair( 'Content-type', $mimeType );
		return json_encode( $data );
	}

	protected function negotiateContentType(){
		$supported	= array( 'text/json', 'text/html' );
		$request	= $this->env->getRequest();

//		var_dump( $request->getHeaders() )'  
		$accepts	= $request->getHeadersByName( 'accept' );
		foreach( $accepts as $accept ){
			$types	= array_keys( $accept->getValue( true ) );
			foreach( $types as $type )
				if( in_array( $type, $supported ) )
					return $type;
		}
		return $supported[0];
	}
	
	protected function respond( $body, $headers = array() ){
		$config		= $this->env->getConfig();
		$request	= $this->env->getRequest();
		$response	= $this->env->getResponse();

		switch( $this->negotiateContentType() ){
			case 'text/html':
				$channel	= new Browser( $this->env );
				$body		= $channel->render( $body, $headers );
				break;
			case 'text/json':
				return parent::respond( $body, $headers );
		}
		return parent::respond( $body, $headers );
	}
	
	protected function throw401()
	{
		$this->env->getResponse()->setStatus( '401 Unauthorized' );
		$heading	= UI_HTML_Tag::create( 'h1', '401 Unauthorized' );
		$paragraph	= UI_HTML_Tag::create( 'p', 'You need to send a token, which you get by posting the shared secret to /auth/getToken.' );
		$page	= new UI_HTML_PageFrame();
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/reset.css' );
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/typography.css' );
		$page->addBody( $heading.$paragraph );
		$page	= $page->build( array( 'style' => 'margin: 2em' ) );
		$this->respond( $page );
		$this->logOnComplete();
		$this->env->close();
		exit( 1 );
	}
	protected function throw403()
	{
		$this->env->getResponse()->setStatus( '403 Forbidden' );
		$heading	= UI_HTML_Tag::create( 'h1', '403 Forbidden' );
		$paragraph	= UI_HTML_Tag::create( 'p', 'This service can not be accessed by your IP address.' );
		$page	= new UI_HTML_PageFrame();
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/reset.css' );
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/typography.css' );
		$page->addBody( $heading.$paragraph );
		$page	= $page->build( array( 'style' => 'margin: 2em' ) );
		$this->respond( $page );
		$this->logOnComplete();
		$this->env->close();
		exit( 1 );
	}

	protected function throw404()
	{
		$this->env->getResponse()->setStatus( '404 Not Found' );
		$heading	= UI_HTML_Tag::create( 'h1', '404 Not Found' );
		$paragraph	= UI_HTML_Tag::create( 'p', 'The resource you have requested is not existing on this server.' );
		$page	= new UI_HTML_PageFrame();
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/reset.css' );
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/typography.css' );
		$page->addBody( $heading.$paragraph );
		$page	= $page->build( array( 'style' => 'margin: 2em' ) );
		$this->respond( $page );
		$this->logOnComplete();
		$this->env->close();
		exit( 1 );
	}
/*
	protected function throw500( $reason )
	{
		$this->env->getResponse()->setStatus( '500 Internal Server Error' );
		$heading	= UI_HTML_Tag::create( 'h1', '500 Internal Server Error' );
		$paragraph	= UI_HTML_Tag::create( 'p', 'An exception occured while executing your request. Please restart the service.' );
		$page	= new UI_HTML_PageFrame();
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/reset.css' );
		$page->addStylesheet( '//css.ceusmedia.de/blueprint/typography.css' );
		$page->addBody( $heading.$paragraph );
		$page	= $page->build( array( 'style' => 'margin: 2em' ) );
		$this->respond( $page );
		$this->logOnComplete();
		$this->env->close();
		exit( 1 );
	}*/

	protected function validateReferer(){
		$refererAllowed	= trim( $this->env->config->get( 'module.server_json.referers.only' ) );	//  get allowed referers from config
		if( !$refererAllowed )																		//  no referers defined
			return TRUE;																			//  so everyone can access

		$refererActual	= parse_url( getEnv( 'HTTP_REFERER' ) );									//  get actually requesting referer
		if( empty( $refererActual['host'] )	)														//  no valid referer transmitted
			return FALSE;																			//  so block access

		$referers	= preg_split( '/\s,\s/', $refererAllowed );										//  if multiple referers are defined
		if( in_array( $refererActual['host'], $referers ) )											//  match allowed referer host with requesting referer host
			return TRUE;																			//  allowed
		return FALSE;																				//  otherwise block
	}
}
?>