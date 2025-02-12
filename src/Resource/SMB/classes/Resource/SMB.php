<?php

use CeusMedia\HydrogenFramework\Environment;
use Icewind\SMB\BasicAuth as SmbBasicAuth;
use Icewind\SMB\IServer as SmbServerInterface;
use Icewind\SMB\IShare as SmbShareInterface;
use Icewind\SMB\ServerFactory as SmbServerFactory;
class Resource_SMB
{
	public const MODE_LAZY			= 0;
	public const MODE_STRAIGHT 		= 1;

	protected Environment $env;

	protected array $connectionData	= [
		'host'			=> '',
		'username'		=> '',
		'workgroup'		=> '',
		'password'		=> '',
		'share'			=> '',
	];

	protected ?SmbServerInterface $connection	= NULL;
	protected ?SmbShareInterface $share			= NULL;
	protected int $status						= 0;
	protected string $path						= '';

	public function __construct( Environment $env )
	{
		$this->env		= $env;
	}

	public function connect( string $host, string $username, string $workgroup, string $password, string $share, int $mode = self::MODE_LAZY ): void
	{
		$this->connectionData['host']		= $host;
		$this->connectionData['username']	= $username;
		$this->connectionData['workgroup']	= $workgroup;
		$this->connectionData['password']	= $password;
		$this->connectionData['share']		= $share;
		if( self::MODE_STRAIGHT === $mode )
			$this->openConnection();
	}

	/**
	 * @param string $path
	 * @param $pattern
	 * @return \Icewind\SMB\IFileInfo[]
	 * @throws \Icewind\SMB\Exception\InvalidTypeException
	 * @throws \Icewind\SMB\Exception\NotFoundException
	 */
	public function index( string $path = './', $pattern = NULL ): array
	{
		$this->openConnection();
		foreach( $this->share->dir( $this->path.$path ) as $item ){
			if( NULL !== $pattern && 1 !== preg_match( $pattern, $item->getName() ) )
				continue;
			$type	= $item->isDirectory() ? 'dir' : 'file';
			$list[$type.'_'.$item->getName()]	= $item;
		}
		ksort( $list );
		return array_values( $list );
	}

	public function has( string $path ): bool
	{
		$this->openConnection();
		try{
			$this->share->stat( $this->path.$path );
			return TRUE;
		}
		catch( Throwable ){
			return FALSE;
		}
	}

	public function get( string $path, string $targetFile ): bool
	{
		$this->openConnection();
		return $this->share->get( $this->path.$path, $targetFile );
	}

	public function remove( string $path ): bool
	{
		$this->openConnection();
		return $this->share->del( $this->path.$path );
	}

	public function set( string $path, string $content ): bool
	{
		$tmpfile	= tempnam( sys_get_temp_dir(), uniqid('SMB', TRUE ) );
		$handle		= fopen( $tmpfile, 'w' );
		fwrite( $handle, $content );
		fclose( $handle );
		$result		= $this->uploadFile( $path, $tmpfile );
		unlink( $tmpfile );
		return $result;
	}

	public function setPath( string $path ): self
	{
		$this->path	= rtrim( trim( $path ), '/' ).'/';
		return $this;
	}

	public function uploadFile( string $filePath, string $targetFileName ): bool
	{
		$this->openConnection();
		return $this->share->put( $this->path.$targetFileName, $filePath );
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
		$serverFactory		= new SmbServerFactory();
		$this->connection	= $serverFactory->createServer(
			$this->connectionData['host'],
			new SmbBasicAuth(
				$this->connectionData['username'],
				$this->connectionData['workgroup'],
				$this->connectionData['password']
			)
		);
		$this->share		= $this->connection->getShare( $this->connectionData['share'] );
	}
}