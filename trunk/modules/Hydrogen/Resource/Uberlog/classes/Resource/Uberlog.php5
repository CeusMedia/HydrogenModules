<?php
class Resource_Uberlog{

	protected $url;
	protected $category;

	public function __construct( $url, $category ){
		$this->url		= $url;
		$this->category	= $category;
		$this->host		= getEnv( 'HTTP_HOST' );
	}

	public function report( $data ){
		if( !array_key_exists( 'category', $data ) && $this->category )
			$data['category']	= $this->category;
		if( !array_key_exists( 'host', $data ) && $this->host )
			$data['host']	= $this->host;
		$curl	= new Net_CURL( $this->url.'/record' );
		$curl->setOption( CURLOPT_RETURNTRANSFER, TRUE );
		$curl->setOption( CURLOPT_POST, TRUE );
		$curl->setOption( CURLOPT_POSTFIELDS, $data );
		return $curl->exec();
	}
}
?>