<?php
class Logic_FTP{

		/**	@var	File_Cache	$cache */
	protected $cache;
	/**	@var	Net_FTP_Client	$client */
	protected $client;

	protected $cachePrefix;

	public function __construct(){
		$this->cache	= new File_Cache( 'contents/cache/' );
	}

	public function connect( $host, $port, $username, $password, $path ){
		$this->client	= new Net_FTP_Client( $host, $port, $path, $username, $password );
		$this->cachePrefix	= 'ftp_'.$host.$path.'_';
	}

	protected function checkConnection(){
		if( !$this->client )
			throw new RuntimeException( 'Not connected' );
	}

	public function countFiles( $path ){
		$entries	= $this->index( $path );
		$number		= 0;
		foreach( $entries as $entry )
			if( !$entry['isdir'] )
				$number++;
		return $number;
	}

	public function countFolders( $path ){
		$entries	= $this->index( $path );
		$number		= 0;
		foreach( $entries as $entry )
			if( $entry['isdir'] )
				$number++;
		return $number;
	}

	public function index( $path = "/" ){
		$this->checkConnection();
		if( ( $data = $this->cache->get( $this->cachePrefix.'path_'.$path ) ) )
			return $data;
		$list	= $this->client->getList( $path );
		$this->cache->set( $this->cachePrefix.'path_'.$path, $list );
		return $list;
	}

	public function isConnected(){
		return (bool) $this->client;
	}

	public function uncache( $path ){
		return $this->cache->remove( $this->cachePrefix.'path_'.$path );
	}
}
?>
