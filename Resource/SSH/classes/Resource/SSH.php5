<?php
/**
 *	@uses		phpseclib\Net\SSH2
 *	@uses		phpseclib\Net\SCP
 */
class Resource_SSH{

	const MODE_LAZY				= 0;
	const MODE_STRAIGHT 		= 1;

	protected $connection;
	protected $env;
	protected $host;
	protected $mode;
	protected $path;
	protected $port;
	protected $privateKey;
	protected $username;
	protected $scp;
	protected $status			= 0;

	public function __construct( $env ){
		$this->env		= $env;
	}

	public function connect( $host, $username, $privateKey, $port = 22, $mode = self::MODE_LAZY ){
		$this->host			= $host;
		$this->port			= $port;
		$this->username		= $username;
		$this->privateKey	= $privateKey;
		$this->mode			= $mode;
		if( $mode === self::MODE_STRAIGHT )
			$this->_connect();
	}

	protected function _connect( $forceReconnect = FALSE ){
		if( $this->connection && !$forceReconnect )
			return;
		$key = new \phpseclib\Crypt\RSA();
		if( substr( $this->privateKey, 0, 10 ) === '-----BEGIN' )
			$key->loadKey( $this->privateKey );
		else if( file_exists( $this->privateKey ) )
			$key->loadKey( file_get_contents( $this->privateKey ) );
		else
			throw Exception( 'Neither valid key string nor key file given' );

		$connection = new \phpseclib\Net\SSH2( $this->host, $this->port );
		if( !$connection->login( $this->username, $key ) )
			throw RuntimeException( sprintf( 'Login as %s failed', $this->username ) );
		$this->connection	= $connection;
	}

	public function pwd(){
		return $this->_exec( 'pwd' );
	}

	public function index( $path = './', $pattern = NULL ){
		$options	= array();
		$options[]	= 'a';																//  show all files/folders
		$options[]	= 'h';																//  show hidden files/folders
		$options	= count( $options ) ? '-'.join( $options ) : '';					//  collect command options
		$command	= sprintf( 'ls %s %s', $options, $this->path.$path );				//  render shell command
		$list		= explode( PHP_EOL, trim( $this->_exec( $command ) ) );				//  execute command and split resulting lines
		foreach( $list as $nr => $item )												//  iterate resulting lines
			if( in_array( $item, array( '.', '..' ) ) )									//  if line is current or parent folder
				unset( $list[$nr] );													//  remove from resulting lines
		$list	= array_values( $list );												//  re-index resulting lines
		if( $pattern ){
			foreach( $list as $nr => $item ){
				if( 0 ){
					unset( $list[$nr] );
				}
			}
		}
		return array_values( $list );
	}

	public function has( $path ){
		$this->_connect();
		$command	= sprintf( 'test -e %s', $this->path.$path.' && echo 1' );			//  render shell command
		return $this->connection->exec( $command );
	}

	public function get( $path ){
		$this->_initScp();
		return $this->scp->get( $this->path.$path );
	}

	public function remove( $path ){
		$this->_connect();
		$command	= sprintf( 'rm -R %s', $this->path.$path.' && echo 1' );
		return $this->connection->exec( $command );
	}

	public function set( $path, $content ){
		$this->_initScp();
		return $this->scp->put( $this->path.$path, $content );
	}

	public function setPath( $path ){
		$this->path	= rtrim( trim( $path ), '/' ).'/';
	}

	protected function _exec( $command ){
		$this->_connect();
		return $this->connection->exec( $command );
	}

	protected function _initScp(){
		if( $this->scp )
			return;
		$this->_connect();
		$this->scp	= new \phpseclib\Net\SCP( $this->connection );
	}
}
