<?php
/**
 *	Resource to communicate with chat server.
 *
 *	Copyright (c) 2010 Christian Würker (ceusmedia.com)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Reader as HttpReader;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	Resource to communicate with chat server.
 *	@category		cmFrameworks
 *	@package		Hydrogen.Environment.Resource.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Christian Würker
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class Resource_Server_Json
{
	protected $env;
	protected $serverUri;
	protected $curlOptions		= array(
		'ALL'	=> [],
		'GET'	=> [],
		'POST'	=> []
	);
	protected $userAgent		= 'CMF:Hydrogen/1.0';

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env	Environment
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$config		= $env->getConfig();
		$module		= new Dictionary( $config->getAll( 'module.resource_server_json.', TRUE ) );

		$this->serverUri	= $module->get( 'uri' );
		if( empty( $this->serverUri ) )
			throw new RuntimeException( 'No server URI set in module config (Resource_Server_JSON:uri)' );

		if( $module->get( 'userAgent' ) )
			$this->userAgent		= $module->get( 'userAgent' );
		$this->setCurlOption( CURLOPT_USERAGENT, $this->userAgent );

		if( $module->get( 'auth.username' ) ){
			$userpwd	= $module->get( 'auth.username' ).':'.$module->get( 'auth.password' );
			$this->setCurlOption( CURLOPT_USERPWD, $userpwd );
		}

		if( $env->getBaseUrl() ) {
			$parts		= parse_url( $env->getBaseUrl() );
			$referer	= $parts['scheme'].'://'.$parts['host'].getEnv( 'REQUEST_URI' );
			$this->setCurlOption( CURLOPT_REFERER, $referer );
		}

		$this->clientIp		= getEnv( 'REMOTE_ADDR' );
	}

	/**
	 *	@todo			make environment resource key configurable
	 *	@todo			allow multiple instances
	 *	@todo			localization of messages
	 *	@todo			allow other auth methods than 'shared secred'
	 */
	public static function ___onEnvInit( Environment $env, $context, $module, $data = [] )
	{
		$server		= new Resource_Server_Json( $context );
		$context->set( 'server', $server );
		$config		= $context->getConfig();
		$session	= $context->getSession();
		try{
			$token		= $session->get( 'token' );
			if( $token && !$server->postData( 'auth', 'validateToken' ) )
				$session->set( 'token', $token = NULL );
			if( !$token ) {																				//  client has no token yet
				$session->set( 'ip', getEnv( 'REMOTE_ADDR' ) );											//  store ip address in session
				$data	= array(
					'credentials'	=> array(															//  prepare POST data
						'secret'	=> $config->get( 'module.resource_server_json.auth.secret' ),		//  with known secret
					)
				);
				$token	= $server->postData( 'auth', 'getToken', NULL, $data );							//  request token from server using POST request
				$session->set( 'token', $token );														//  store token in session
			}
		}
		catch( Exception $e ){
			$message	= "Der Chat-Server ist momentan nicht erreichbar.";
			$env->getMessenger()->noteFailure( $message );
			return;
		}

	}

	/**
	 *	Returns set CURL option by its key.
	 *	@access		public
	 *	@param		string		$key		CURL option key
	 *	@param		string		$method		Request method (ALL|GET|POST)
	 *	@param		bool		$strict		Flag: throw exception or return NULL
	 *	@return		mixed		Set CURL option value or NULL (if not strict)
	 *	@throws		InvalidArgumentException if method is invaid
	 *	@throws		InvalidArgumentException if key is not existing and strict mode
	 */
	public function getCurlOption( $key, $method = 'ALL', $strict = FALSE )
	{
		$method	= strtoupper( $method );
		if( !array_key_exists( $method, $this->curlOptions ) )
			throw new InvalidArgumentException( 'Invalid method: '.$method );
		if( isset( $this->curlOptions[$method][$key] ) )
			return $this->curlOptions[$method][$key];
		if( $strict )
			throw new InvalidArgumentException( 'Invalid option key: '.$key );
		return NULL;
	}

	public function getCurlOptions( $method = 'ALL' )
	{
		$method	= strtoupper( $method );
		if( !array_key_exists( $method, $this->curlOptions ) )
			throw new InvalidArgumentException( 'Invalid method: '.$method );
		return $this->curlOptions[$method];
	}

	public function getData( $controller, $action = NULL, $arguments = [], $parameters = [], $curlOptions = [] )
	{
		$url	= $this->buildServerGetUrl( $controller, $action, $arguments, $parameters = [] );
		return	$this->getDataFromUrl( $url, $curlOptions );
	}

	public function getDataFromUri( $uri, $curlOptions = [] )
	{
		return $this->getDataFromUrl( $this->serverUri.$uri, $curlOptions );
	}

	public function getDataFromUrl( $url, $curlOptions = [] )
	{
		$reader		= new HttpReader();
		$headers	= ['Accept-Encoding: gzip, deflate'];
		$options	= $this->curlOptions['ALL'] + $this->curlOptions['GET'] + $curlOptions;
		$response	= $reader->get( $url, $headers, $options );
		$json		= $response->getBody();

		$statusCode	= $reader->getCurlInfo( Net_CURL::INFO_HTTP_CODE );
		$logPath	= $this->env->config->get( 'path.logs' );
		$logEnabled	= $this->env->config->get( 'module.resource_server_json.log' );
		$logFile	= $this->env->config->get( 'module.resource_server_json.log.file' );
		if( $logEnabled && $logFile )
			error_log( time()." GET (".$statusCode."): ".$json."\n", 3, $logPath.$logFile );
		$response	= $this->handleResponse( $json, $url, $statusCode );
		return $response->data;
	}

	public function postData( $controller, $action = NULL, $arguments = NULL, $data = [], $curlOptions = [] )
	{
		$url	= $this->buildServerPostUrl( $controller, $action, $arguments );
		return $this->postDataToUrl( $url, $data, $curlOptions );
	}

	public function postDataToUri( $uri, $data = [], $curlOptions = [] )
	{
		return $this->postDataToUrl( $this->serverUri.$uri, $data, $curlOptions );
	}

	public function postDataToUrl( $url, $data = [], $curlOptions = [] )
	{
		if( $data instanceof Dictionary )
			$data	= $data->getAll();
		if( $this->env->getSession()->get( 'token' ) )
			$data['token']	= $this->env->getSession()->get( 'token' );
		if( $this->env->getSession()->get( 'ip' ) )
			$data['ip']	= $this->env->getSession()->get( 'ip' );
		foreach( $data as $key => $value )															//  cURL hack (file upload identifier)
			if( is_string( $value ) && substr( $value, 0, 1 ) == "@" )								//  leading @ in field values
				$data[$key]	= "\\".$value;															//  need to be escaped

		$reader		= new HttpReader();
		$headers	= ['Accept-Encoding: gzip, deflate'];
		$headers	= ['Accept: text/json'];
		$curlOptions[CURLOPT_POST]	= TRUE;
		$curlOptions[CURLOPT_POSTFIELDS] = http_build_query( $data );
		$options	= $this->curlOptions['ALL'] + $this->curlOptions['POST'] + $curlOptions;
		$response	= $reader->post( $url, $data, $headers, $options );
		$json		= $response->getBody();

		$statusCode	= $reader->getCurlInfo( Net_CURL::INFO_HTTP_CODE );
		$logPath	= $this->env->config->get( 'path.logs' );
		$logEnabled	= $this->env->config->get( 'module.resource_server_json.log' );
		$logFile	= $this->env->config->get( 'module.resource_server_json.log.file' );
		if( $logEnabled && $logFile )
			error_log( time()." POST (".$statusCode."): ".$json."\n", 3, $logPath.$logFile );
		$response	= $this->handleResponse( $json, $url, $statusCode );
		return $response->data;
	}

	public function setCurlOption( $key, $value, $method = 'ALL' )
	{
		$method	= strtoupper( $method );
		if( !array_key_exists( $method, $this->curlOptions ) )
			throw new InvalidArgumentException( 'Invalid method: '.$method );
		$this->curlOptions[$method][$key]	= $value;
	}

	public function setCurlOptions( $curlOptions, $method = 'ALL' )
	{
		$method	= strtoupper( $method );
		if( !array_key_exists( $method, $this->curlOptions ) )
			throw new InvalidArgumentException( 'Invalid method: '.$method );
		$this->curlOptions[$method]	= $curlOptions;
	}

	protected function buildServerGetUrl( $controller, $action = NULL, $arguments = [], $parameters = [] )
	{
		$url	= $this->buildServerPostUrl( $controller, $action, $arguments );
		if( is_null( $parameters ) )
			$parameters	= [];
		if( !is_array( $parameters ) )
			throw new InvalidArgumentException( 'Parameters must be an array or NULL' );
		if( $this->env->getSession()->get( 'token' ) )
			$parameters['token']	= $this->env->getSession()->get( 'token' );
		if( $this->env->getSession()->get( 'ip' ) )
			$parameters['ip']	= $this->env->getSession()->get( 'ip' );
		if( $parameters )
			$url	.= '?'.http_build_query( $parameters, NULL, '&' );
		return $url;
	}

	/**
	 *	Builds URL string from controller, action and arguments.
	 *	@access		protected
	 *	@param		string		$controller		Controller name
	 *	@param		string		$action			Action name
	 *	@param		array		$arguments		List of URI arguments
	 *	@return		string		URL on server
	 */
	protected function buildServerPostUrl( $controller, $action = NULL, $arguments = [] )
	{
		if( $arguments && empty( $action ) )
			$action		= 'index';
		if( $action && !$controller )
			$controller	= 'index';
		if( is_string( $controller ) && !empty( $controller ) )
			$controller	= preg_replace( '/([^\/]+)\/?/', '\\1', $controller ).'/';
		if( is_string( $action ) && !empty( $action ) )
			$action		= preg_replace( '/([^\/]+)\/?/', '\\1', $action ).'/';
		if( !is_array( $arguments ) )
			$arguments	= $arguments ? [$arguments] : [];
		foreach( $arguments as $nr => $argument )
			$arguments[$nr]	= urlencode( $argument );
		$arguments	= implode( '/', $arguments );
		$url		= $this->serverUri.$controller.$action.$arguments;
		return $url;
	}

	protected function handleResponse( $json, $url, $statusCode )
	{

		if( $statusCode != 200 && $statusCode != 500 )
			throw new RuntimeException( 'Resource '.$url.' has HTTP code '.$statusCode );
		$response	= json_decode( $json );
		if( !is_object( $response ) )
			throw new RuntimeException( 'Resource '.$url.' is no JSON object' );
		if( empty( $response->exception ) )
			return $response;
		if( empty( $response->serial ) )
			throw new RuntimeException( $response->exception );
		throw unserialize( $response->serial );
	}
}
