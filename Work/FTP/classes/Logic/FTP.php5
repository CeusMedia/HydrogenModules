<?php
class Logic_FTP
{
	/**	@var	File_Cache	$cache */
	protected $cache;

	/**	@var	Net_FTP_Client	$client */
	protected $client;

	protected $cachePrefix;

	public function __construct( string $pathCache = 'contents/cache/' )
	{
		$this->cache	= new FS_File_Cache( $pathCache );
	}

	public function connect( string $host, $port, string $username, string $password, string $path )
	{
		$this->client	= new Net_FTP_Client( $host, $port, $path, $username, $password );
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
