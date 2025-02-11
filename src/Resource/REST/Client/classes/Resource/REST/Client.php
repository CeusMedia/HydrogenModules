<?php
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\Cache\SimpleCacheFactory as CacheFactory;
use CeusMedia\REST\Client as RestClient;

/**
 *	@todo		Code doc
 */
class Resource_REST_Client
{
	protected Environment $env;
	protected Dictionary $session;
	protected Dictionary $moduleConfig;
	protected RestClient $client;
	protected ?object $cache			= NULL;
	protected bool $enabled				= TRUE;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env		Environment instance
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->session		= $this->env->getSession();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_rest_client.', TRUE );
		$this->initClient();
		$this->initLogging();
		$this->initCache();
	}

	/**
	 *	Remove entity.
	 *	@access		public
	 *	@param		string		$path		Resource path
	 *	@param		array		$data		GET parameters
	 *	@return		mixed		Server response
	 */
	public function delete( string $path, array $data = [] )
	{
		$this->invalidateCachePathRecursive( $path );
		return $this->client->delete( $path );
	}

	/**
	 *	@deprecated	use module configuration instead
	 *	@todo		to be removed
	 */
	public function disableCache(): void
	{
		$this->enabled = FALSE;
		$this->initCache();
	}

	/**
	 *	@deprecated	use module configuration instead
	 *	@todo		to be removed
	 */
	public function enableCache(): void
	{
		$this->enabled = TRUE;
		$this->initCache();
	}

	/**
	 *	Set expected response format.
	 *	@access		public
	 *	@param		string		$format		Response format to set
	 *	@return		void
	 */
	public function expectFormat( string $format ): void
	{
		$this->client->expectFormat( $format );
	}

	public function getCacheKey( string $path ): string
	{
		return str_replace( "/", ".", $path );
//		return md5( $path );
	}

	/**
	 *	Read cache content.
	 *	@access		public
	 *	@param		string		$path			Resource path
	 *	@param		array		$parameters		GET parameters
	 *	@return		mixed		Resource content
	 */
	public function get( string $path, array $parameters = [] )
	{
		$isEnabled	= $this->moduleConfig->get( 'cache.enabled' );					//  shortcut cache status
		$isCachable	= $isEnabled && !count( $parameters );							//  also request has no GET parameters
		$cacheKey	= $this->getCacheKey( $path );									//  render cache key
		if( $isCachable && ( $cached = $this->cache->get( $cacheKey ) ) !== NULL )	//  cache hit by cache key
			return $cached;															//  return cached content
		$response	= $this->client->get( $path, $parameters );						//  request resource
		if( isset( $response->data->data ) && $response->data->data === "error" ){
//			$this->lastestResponse	= $response;
			throw new RuntimeException( "Request to server failed: ".$response->data->error );
		}
		if( $isCachable )															//  cache is enabled for request
			$this->cache->set( $cacheKey, $response );								//  cache resource content
		return $response;															//  return resource content
	}

	public function invalidateCachePathRecursive( string $path ): void
	{
		if( !$this->moduleConfig->get( 'cache.enabled' ) )
			return;
		$parts	= explode( "/", $path );
		while( $parts ){
			$cacheKey	= $this->getCacheKey( implode( "/", $parts ) );
			$this->cache->delete( $cacheKey );
			array_pop( $parts );
		}
	}

	/**
	 *	Send data to create new resource.
	 *	@access		public
	 *	@param		string		$path		Resource path
	 *	@param		array		$data		GET parameters
	 *	@return		mixed		Resource content
	 */
	public function post( string $path, array $data = [] )
	{
		$this->invalidateCachePathRecursive( $path );
		return $this->client->post( $path, $data );
	}

	/**
	 *	Send updated resource.
	 *	@access		public
	 *	@param		string		$path		Resource path
	 *	@param		array		$data		GET parameters
	 *	@return		mixed		Resource content
	 */
	public function put( string $path, array $data = [] )
	{
		$this->invalidateCachePathRecursive( $path );
		return $this->client->put( $path, $data );
	}

	public function setAuthToken( string $token ): void
	{
		$this->client->addRequestHeader( 'X-REST-Token', $token );
	}

	/**
	 *	Set credentials for HTTP Basic Authentication.
	 *	@access		public
	 *	@param		string		$username	HTTP Basic Auth username
	 *	@param		string		$password	HTTP Basic Auth password
	 *	@return		void
	 */
	public function setBasicAuth( string $username, string $password ): void
	{
		$this->client->setBasicAuth( $username, $password );
	}

	/**
	 *	@deprecated		use module configuration instead
	 *	@todo			to be removed
	 */
	public function useCache( bool $status = TRUE ): void
	{
		$this->enabled = $status;
		$this->moduleConfig->set( 'cache.enabled', $status );
		$this->initCache();
	}

	protected function initClient(): void
	{
		$options		= $this->moduleConfig->getAll( 'server.', TRUE );
		$curlOptions	= array(
			CURLOPT_SSL_VERIFYHOST	=> $options->get( 'verifyHost' ),
			CURLOPT_SSL_VERIFYPEER	=> $options->get( 'verifyPeer' ),
		);
		$this->client	= new RestClient( $options->get( 'URL' ), $curlOptions );
		$this->client->expectFormat( $options->get( 'format' ) );
		$this->client->setBasicAuth( $options->get( 'username' ), $options->get( 'password' ) );
	}

	protected function initCache(): void
	{
		$config		= $this->moduleConfig->getAll( 'cache.', TRUE );
		if( !$this->moduleConfig->get( 'cache.enabled' ) )
			return;
		if( !class_exists( '\CeusMedia\Cache\Factory' ) )
			throw new RuntimeException( 'Cache library "CeusMedia/Cache" is not installed' );
		$type		= $config->get( 'type' ) ?: 'NOOP';
		$resource	= $config->get( 'resource' );
		$context	= $config->get( 'context' );
		$expiration	= $config->get( 'expiration' );

		/*		$type		= 'Session';
				$resource	= md5( getCwd() );
				$context	= 'cache.';*/

		$this->cache	= CacheFactory::createStorage( $type, $resource, $context );
	}

	protected function initLogging(): void
	{
		$pathLogs	= $this->env->getConfig()->get( 'path.logs' );
		$options	= $this->moduleConfig->getAll( 'log.', TRUE );
		if( $options->get( 'requests' ) ){
			$filePath	= $pathLogs.$options->get( 'requests' );
			if( !file_exists( dirname( $filePath ) ) )
				FolderEditor::createFolder( $filePath );
			$this->client->setLogRequests( $filePath );
		}
		if( $options->get( 'errors' ) ){
			$filePath	= $pathLogs.$options->get( 'errors' );
			if( !file_exists( dirname( $filePath ) ) )
				FolderEditor::createFolder( $filePath );
			$this->client->setLogErrors( $filePath );
		}
	}
}
