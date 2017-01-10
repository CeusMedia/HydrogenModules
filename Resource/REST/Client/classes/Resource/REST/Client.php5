<?php
/**
 *	@todo		Code doc
 */
class Resource_REST_Client{

	protected $cache;
	protected $client;
	protected $enabled	= TRUE;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Environment instance
	 *	@return		void
	 */
	public function __construct( $env ){
		$this->env			= $env;
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_rest_client.', TRUE );
		$this->__initClient();
		$this->__initCache();
	}

	protected function __initClient(){
		$config				= $this->moduleConfig()->getAll( 'server.', TRUE );
		$options			= array();
		$this->client		= new \CeusMedia\REST\Client( $config->get( 'URL' ), $options );
		$this->client->expectFormat( $config->get( 'format' ) );
		if( $config->get( 'username' ) )
			$client->setBasicAuth( $config->get( 'username' ), $config->get( 'password' ) );
	}

	protected function __initCache(){
		$config		= $this->moduleConfig->getAll( 'cache.', TRUE );
		if( !$this->moduleConfig->get( 'cache.enabled' ) )
			return;
		if( !class_exists( '\CeusMedia\Cache\Factory' ) )
			throw new RuntimeException( 'Cache library "CeusMedia/Cache" is not installed' );
		$type		= $config->get( 'enabled' ) ? $config->get( 'type' ) : 'NOOP';
		$resource	= $config->get( 'resource' );
		$context	= $config->get( 'context' );
		$expiration	= $config->get( 'expiration' );

		$type		= 'Session';
		$resource	= md5( getCwd() );
		$context	= 'cache.';

		$this->cache	= \CeusMedia\Cache\Factory::createStorage( 'Session', md5( getCwd() ), 'cache.' );
	}

	/**
	 *	Clear cache completely with in context.
	 *	@access		public
	 *	@return		void
	 */
	public function clear(){
		$this->client->flush();
	}

	/**
	 *	Remove cache content.
	 *	@access		public
	 *	@param		string		$path		Resource path
	 *	@param		array		$data		GET parameters
	 *	@return		mixed		Server response
	 */
	public function delete( $path, $data = array() ){
		$this->invalidateCachePathRecursive( $path );
		return $this->client->delete( $path, $data );
	}

	/**
	 *	@deprecated	use module configuration instead
	 *	@todo		to be removed
	 */
	public function enableCache( $status = TRUE ){
//		$this->enabled = (bool) $status;
		$this->moduleConfig->set( 'cache.enabled', (bool) $status )
		$this->__initCache();
	}

	/**
	 *	Set expected response format.
	 *	@access		public
	 *	@param		string		$format		Response format to set
	 *	@return		void
	 */
	public function expectFormat( $format ){
		$this->client->expectFormat( $format );
	}

	public function getCacheKey( $path ){
		return str_replace( "/", ".", $path );
		return md5( $path );
	}

	/**
	 *	Read cache content.
	 *	@access		public
	 *	@param		string		$path		Resource path
	 *	@param		array		$data		GET parameters
	 *	@return		mixed		Resource content
	 */
	public function get( $path, $parameters = array() ){
		$isEnabled	= $this->moduleConfig->get( 'cache.enabled' )					//  shortcut cache status
		$isCachable	= $isEnabled && !count( $parameters ) );						//  also request has no GET parameters
		$cacheKey	= $this->getCacheKey( $path );									//  render cache key
		if( $isCachable && ( $cached = $this->cache->get( $cacheKey ) ) !== NULL )	//  cache hit by cache key
			return $cached;															//  return cached content
		$response	= $this->client->get( $path, $parameters );						//  request resource
		if( $isCachable )															//  cache is enabled for request
			$this->cache->set( $cacheKey, $response );								//  cache resource content
		return $response;															//  return resource content
	}

	public function invalidateCachePathRecursive( $path ){
		if( !$this->moduleConfig->get( 'cache.enabled' ) )
			return TRUE;
		$parts	= explode( "/", $path );
		while( $parts ){
			$cacheKey	= $this->getCacheKey( implode( "/", $parts ) );
			$this->cache->remove( $cacheKey );
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
	public function post( $path, $data = array() ){
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
	public function put( $path, $data = array() ){
		$this->invalidateCachePathRecursive( $path );
		return $this->client->put( $path, $data );
	}

	/**
	 *	Set credentials for HTTP Basic Authentication.
	 *	@access		public
	 *	@param		string		$username	HTTP Basic Auth username
	 *	@param		string		$password	HTTP Basic Auth password
	 *	@return		void
	 */
	public function setBasicAuth( $username, $password ){
		$this->client->setBasicAuth( $username, $password );
	}
}
?>
