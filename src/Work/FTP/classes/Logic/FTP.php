<?php

use CeusMedia\HydrogenFramework\Environment;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
use CeusMedia\Common\Net\FTP\Client as FtpClient;

class Logic_FTP
{
	/**	@var	SimpleCacheInterface	$cache */
	protected SimpleCacheInterface $cache;

	/**	@var	FtpClient|NULL	$client */
	protected ?FtpClient $client				= NULL;

	protected ?string $cachePrefix				= NULL;

	protected Environment $env;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->cache	= $this->env->getCache();
	}

	public function connect( string $host, $port, string $username, string $password, string $path ): void
	{
		$this->client	= new FtpClient( $host, $port, $path, $username, $password );
		$this->cachePrefix	= 'ftp_'.$host.$path.'_';
	}

	public function countFiles( string $path ): int
	{
		$entries	= $this->index( $path );
		$number		= 0;
		foreach( $entries as $entry )
			if( !$entry['isdir'] )
				$number++;
		return $number;
	}

	public function countFolders( string $path ): int
	{
		$entries	= $this->index( $path );
		$number		= 0;
		foreach( $entries as $entry )
			if( $entry['isdir'] )
				$number++;
		return $number;
	}

	/**
	 *	@todo		remove after testing
	 *	@return		FtpClient
	 */
	public function getClient(): FtpClient
	{
		return $this->client;
	}

	public function index( string $path = '/' )
	{
		$this->checkConnection();
		if( ( $data = $this->cache->get( $this->cachePrefix.'path_'.$path ) ) )
			return $data;
		$list	= $this->client->getList( $path );
		$this->cache->set( $this->cachePrefix.'path_'.$path, $list );
		return $list;
	}

	public function isConnected(): bool
	{
		return (bool) $this->client;
	}

	public function setCache( $cache ): self
	{
		$this->cache	= $cache;
		return $this;
	}

	public function uncache( string $path )
	{
		return $this->cache->remove( $this->cachePrefix.'path_'.$path );
	}

	protected function checkConnection(): void
	{
		if( !$this->client )
			throw new RuntimeException( 'Not connected' );
	}
}
