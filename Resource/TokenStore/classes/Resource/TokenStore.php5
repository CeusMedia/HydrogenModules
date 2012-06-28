<?php
/**
 *	Token Store Singleton.
 *	@category		cmApps
 *	@package		Chat.Server.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: TokenStore.php 2676 2012-04-02 15:40:34Z christian.wuerker $
 */
/**
 *	Token Store Singleton.
 *	This is a singleton implementation - please use static call to getInstance() instead of construction with new.
 *	@category		cmApps
 *	@package		Chat.Server.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: TokenStore.php 2676 2012-04-02 15:40:34Z christian.wuerker $
 *	@todo			problem: several clients behind same IP will have same token
 *	@todo			concept: binding token to unique client id and unique client instance id
 */
class Resource_TokenStore {

	/**	@var	Model_Token		$token		Token storage in database */
	protected $model;
	protected static $instance;
	
	/**
	 *	Constructor, not callable. Use Resource_TokenStore::getInstance( $env ) instead.
	 *	@access		protected
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env	Environment object
	 *	@return		void
	 */
	protected function __construct( CMF_Hydrogen_Environment_Abstract $env ) {
		$this->env		= $env;																		//  store environment
		$this->model	= new Model_Token( $this->env );											//  create new token store model
		$this->config	= $this->env->getConfig();													//  shurtcut configuration
		$this->cleanUpStore();																		//  clean up token store
	}

	/**
	 *	Cloning is disabled according to singleton implementation.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __clone(){}

	/**
	 *	Returns singleton instance.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env	Environment object
	 *	@return		Resource_TokenStore
	 */
	public static function getInstance( CMF_Hydrogen_Environment_Abstract $env ){
		if( !self::$instance )
			self::$instance	= new Resource_TokenStore( $env );
		return self::$instance;
	}

	/**
	 *	Returns calculated token.
	 *	@access		public
	 *	@return		string
	 */
	protected function calculateToken(){
		$config	= new ADT_List_Dictionary( $this->config->getAll( 'module.resource_tokenstore.') );	//  extract module configuration
		$credentials	= array();
		$credentials['ip']	= $this->getClientIp();													//  use remote IP as credential
		if( $config->get( 'secret' ) )																//  use secret as credential
			$credentials['secret']		= $config->get( 'secret' );
		if( $config->get( 'bind.protocol' ) )														//  use request protocol as credential
			$credentials['protocol']	= getEnv( 'SERVER_PROTOCOL' );
		if( $config->get( 'bind.host' ) )															//  use host name as credential
			$credentials['host']		= getEnv( 'HTTP_HOST' );
		if( $config->get( 'bind.time' ) )															//  use micro time as credential
			$credentials['timestamp']	= microtime( TRUE );
		$seed	= implode( '|', $credentials );														//  pack credentials
		$salt	= $config->get( 'salt' );															//  get salt to use for hash generation
		return md5( $salt.$seed );
	}

	/**
	 *	Detects client IP.
	 *	@access		protected
	 *	@return		string
	 *	@todo		Security: given IP over GET seems to be risky
	 *	@todo		Sanity: return REMOVE_ADDR by default makes no sense, since the web site server IP is used in request
	 */
	protected function getClientIp() {
		if( $this->env->getRequest()->isAjax() )													//  HTTP request is using AJAX
			return getEnv( 'REMOTE_ADDR' );															//  return request sender IP (since JavaScript is executed in browser)
		if( $this->env->getRequest()->getFromSource( 'ip', 'POST' ) )								//  an IP has been given by POST request
			return $this->env->getRequest()->getFromSource( 'ip', 'POST' );							//  return this IP
		if( $this->env->getRequest()->getFromSource( 'ip', 'GET' ) )								//  an IP has been given by GET request
			return $this->env->getRequest()->getFromSource( 'ip', 'GET' );							//  return this IP
		if( getEnv( 'REMOTE_ADDR' ) == "::1" )														//  local request using IPv6
			return '127.0.0.1';																		//  return local IPv4
		return getEnv( 'REMOTE_ADDR' );																//  @todo	kriss: remove or replace by exception
	}

	public function getToken( $credentials ) {
		$config	= $this->env->getConfig();
		$ip		= $this->getClientIp();																//  get IP of client
		if( $config->get( 'module.resource_tokenstore.secret' ) )									//  a common secret is 
			if( !$this->verifySecret( $credentials ) )												//  secret in given credentials is not matching
				throw new RuntimeException( 'Secret invalid' );										//  break with exception
		$token	= $this->calculateToken();															//  generate a new token string
		$this->model->removeByIndex( 'ip', $ip );													//  remove old token bound to this IP
		$this->model->add( array( 'token' => $token, 'ip' => $ip, 'timestamp' => time() ) );		//  store token in database
		return $token;
	}

	public function hasToken() {
		$ip		= $this->getClientIp();																//  get IP of client
		return (bool) $this->model->countByIndex( 'ip', $ip );										//  indicate found token
	}

	/**
	 *	Loads map of stored tokens from configured store file.
	 *	Removes outdated tokens, if token lifetime is configured.
	 *	@access		protected
	 *	@return		void
	 *	@todo		prevent read-write-collision of several instances using synchronisation
	 */
	protected function cleanUpStore() {
		$config	= $this->env->getConfig();
		$lifetime	= (int) $config->get( 'module.resource_tokenstore.lifetime' );					//  get token lifetime from module configuration
		if( !$lifetime )																			//  no token lifetime defined
			return 0;																				//  no tokens will be removed
		$maxTimestamp	= time() - $lifetime;														//  timestamp of oldest allowed tokens
		$outdatedTokens	= $this->model->getAll( array( 'timestamp' => '<'.$maxTimestamp ) );		//  find tokens with older timestamp
		foreach( $outdatedTokens as $outdatedToken )												//  iterate outdated tokens
			$this->model->remove( $outdatedToken->tokenId );										//  remove each outdated token
		return count( $outdatedTokens );															//  return number of removed tokens
	}

	/**
	 *	Indicates whether a given token is bound to an IP.
	 *	@access		public
	 *	@param		string		$token		Token to validate
	 *	@throws		RuntimeException		if no token is stored for an IP at all
	 *	@return		boolean 
	 */
	public function validateToken( $token ) {
		$ip		= $this->getClientIp();																//  get IP of client
		$data	= $this->model->getByIndex( 'ip', $ip );											//  try to get a token bound to client IP
		if( !$data )																				//  no token found for IP
			throw new RuntimeException( 'No token registered for IP '.$ip );						//  break with exception
		return $token === $data->token;															//  indicate validity of given token
	}

	protected function verifySecret( $credentials ) {
		$secretConfig	= (string) $this->config->get( 'module.resource_tokenstore.secret' );		//  get common secret from module configuration
		if( !$secretConfig )																		//  no common secret set
			return TRUE;																			//
		$secretSent		= !empty( $credentials['secret'] ) ? $credentials['secret'] : NULL;			//  get secret from given credentials
		return $secretConfig === $secretSent;														//  indicate validity of given secret
	}
}
?>