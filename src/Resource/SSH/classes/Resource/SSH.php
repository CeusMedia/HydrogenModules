<?php
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

use CeusMedia\HydrogenFramework\Environment;
use phpseclib\Crypt\RSA as RsaCrypt;
use phpseclib\Net\SCP as ScpConnection;
use phpseclib\Net\SSH2 as Ssh2Connection;

/**
 */
class Resource_SSH
{
	public const MODE_LAZY			= 0;
	public const MODE_STRAIGHT 		= 1;

	protected ?Ssh2Connection $connection	= NULL;
	protected Environment $env;
	protected string $host;
	protected int $mode;
	protected string $path;
	protected int $port;
	protected string $privateKey;
	protected string $username;
	protected ?ScpConnection $scp	= NULL;
	protected int $status			= 0;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
	}

	public function connect( string $host, string $username, string $privateKey, int $port = 22, int $mode = self::MODE_LAZY ): void
	{
		$this->host			= $host;
		$this->port			= $port;
		$this->username		= $username;
		$this->privateKey	= $privateKey;
		$this->mode			= $mode;
		if( $mode === self::MODE_STRAIGHT )
			$this->openConnection();
	}

	public function pwd(): string
	{
		return $this->exec( 'pwd' );
	}

	public function index( string $path = './', $pattern = NULL ): array
	{
		$options	= [];
		$options[]	= 'a';																//  show all files/folders
		$options[]	= 'h';																//  show hidden files/folders
		$options	= count( $options ) ? '-'.join( $options ) : '';					//  collect command options
		$command	= sprintf( 'ls %s %s', $options, $this->path.$path );				//  render shell command
		$list		= explode( PHP_EOL, trim( $this->exec( $command ) ) );				//  execute command and split resulting lines
		foreach( $list as $nr => $item )												//  iterate resulting lines
			if( in_array( $item, ['.', '..'] ) )									//  if line is current or parent folder
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

	public function has( string $path )
	{
		$this->openConnection();
		$command	= sprintf( 'test -e %s', $this->path.$path.' && echo 1' );			//  render shell command
		return $this->connection->exec( $command );
	}

	public function get( string $path )
	{
		$this->initScp();
		return $this->scp->get( $this->path.$path );
	}

	public function remove( string $path )
	{
		$this->openConnection();
		$command	= sprintf( 'rm -R %s', $this->path.$path.' && echo 1' );
		return $this->connection->exec( $command );
	}

	public function set( string $path, string $content )
	{
		$this->initScp();
		return $this->scp->put( $this->path.$path, $content );
	}

	public function setPath( string $path ): self
	{
		$this->path	= rtrim( trim( $path ), '/' ).'/';
		return $this;
	}

	protected function exec( string $command )
	{
		$this->openConnection();
		return $this->connection->exec( $command );
	}

	/**
	 *	@return		void
	 *	@throws		InvalidArgumentException	if no key string or file is given
	 *	@throws		RuntimeException			if login failed
	 */
	protected function initScp(): void
	{
		if( $this->scp )
			return;
		$this->openConnection();
		$this->scp	= new ScpConnection( $this->connection );
	}

	/**
	 *	@param		bool		$forceReconnect
	 *	@return		void
	 *	@throws		InvalidArgumentException	if no key string or file is given
	 *	@throws		RuntimeException			if login failed
	 */
	protected function openConnection( bool $forceReconnect = FALSE ): void
	{
		if( $this->connection && !$forceReconnect )
			return;
		$key = new RsaCrypt();
		if( str_starts_with( $this->privateKey, '-----BEGIN' ) )
			$key->loadKey( $this->privateKey );
		else if( file_exists( $this->privateKey ) )
			$key->loadKey( file_get_contents( $this->privateKey ) );
		else
			throw new InvalidArgumentException( 'Neither valid key string nor key file given' );

		$connection = new Ssh2Connection( $this->host, $this->port );
		if( !$connection->login( $this->username, $key ) )
			throw new RuntimeException( sprintf( 'Login as %s failed', $this->username ) );
		$this->connection	= $connection;
	}
}
