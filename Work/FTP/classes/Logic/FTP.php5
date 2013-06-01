<?php
class Logic_FTP{

		/**	@var	File_Cache	$cache */
	protected $cache;
	/**	@var	Net_FTP_Client	$client */
	protected $client;

	public function __construct(){
		$this->cache	= new File_Cache( 'contents/cache/' );
	}

	public function connect( $host, $port, $username, $password, $path ){
		$this->client	= new Net_FTP_Client( $host, $port, $path, $username, $password );
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
		if( !$this->client )
			throw new RuntimeException( 'Not connected' );
		if( $this->cache->has( 'ftp_'.urlencode( $path ) ) )
			return $this->cache->get( 'ftp_'.urlencode( $path ) );
		$list	= $this->client->getList( $path );
		$this->cache->set( 'ftp_'.urlencode( $path ), $list );
		return $list;
	}

	public function isConnected(){
		return (bool) $this->client;
	}

	public function uncache( $path ){
		return $this->cache->remove( 'ftp_'.urlencode( $path ) );
	}
}
?>
