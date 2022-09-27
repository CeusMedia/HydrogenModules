<?php

use CeusMedia\Common\FS\File\Cache as FileCache;
use CeusMedia\Common\Net\FTP\Client as FtpClient;

class Logic_FTP
{
	/**	@var	FileCache	$cache */
	protected $cache;

	/**	@var	FtpClient	$client */
	protected $client;

	protected $cachePrefix;

	public function __construct( string $pathCache = 'contents/cache/' )
	{
		$this->cache	= new FileCache( $pathCache );
	}

	public function connect( string $host, $port, string $username, string $password, string $path )
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
	 *	@return		Net_FTP_Client
	 */
	public function getClient()
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

	protected function checkConnection()
	{
		if( !$this->client )
			throw new RuntimeException( 'Not connected' );
	}
}
