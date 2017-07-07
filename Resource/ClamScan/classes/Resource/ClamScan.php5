<?php
class Resource_ClamScan{

	static public $socketPath	= '/var/run/clamav/clamd.ctl';
	protected $socket;

	public function __construct(){
		$this->openSocket();
	}

	public function __destruct(){
		$this->closeSocket();
	}

	protected function closeSocket(){
		if( $this->socket && is_resource( $this->socket ) )
			fclose( $this->socket );
	}

	public function scanFile( $filePath ){
		if( !file_exists( $filePath ) )
			throw new RuntimeException( 'File "'.$filePath.'" is not existing' );
		if( !$this->socket )
			$this->openSocket();
		$filePath	= realpath( $filePath );
		$command	= "SCAN ".$filePath;
		fputs( $this->socket, $command );
		$response	= fgets( $this->socket, 4096 );
		$this->closeSocket();

		$parts		= explode( ': ', $response, 2 );
		if( $parts[0] !== $filePath )
			throw new RuntimeException( "Response not understood: ". $response );
		$parts[2]	= explode( ' ', $parts[1] );
		$status		= array_pop( $parts[2] );
		return (object) array(
			'file'		=> $filePath,
			'clean'		=> trim( $status ) === 'OK',
			'status'	=> trim( $status ),
			'message'	=> join( ' ', $parts[2] ),
		);
	}

	protected function openSocket(){
		$this->closeSocket();
		$this->socket	= @fsockopen( 'unix://'.self::$socketPath, -1, $errno, $errstr, 2 );
		if( !$this->socket ){
			$msg	= 'Socket connection to clamav daemon failed: %s (%s)';
			throw new RuntimeException( sprintf( $msg, $errno, $errstr ) );
		}
	}
}
?>
