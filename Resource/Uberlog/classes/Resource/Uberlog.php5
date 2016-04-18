<?php
class Resource_Uberlog{

	protected $url;
	protected $category;

	public function __construct( $env ){
		$this->env		= $env;
		$this->url		= $env->getConfig()->get( 'module.resource_uberlog.uri' );
		$this->category	= "test";
		$this->host		= getEnv( 'HTTP_HOST' );
		$this->client	= $env->getConfig()->get( 'app.name' );
		$this->userAgent	= getEnv( 'HTTP_USER_AGENT' );
	}

	public function report( $data ){
		if( $data instanceof Exception ){
			$data	= array(
				'message'	=> $data->getMessage(),
				'code'		=> $data->getCode(),
				'source'	=> $data->getFile(),
				'line'		=> $data->getLine(),
				'type'		=> get_class( $data ),
			);
		}

		if( !array_key_exists( 'category', $data ) && $this->category )
			$data['category']	= $this->category;
		if( !array_key_exists( 'client', $data ) && $this->client )
			$data['client']	= $this->client;
		if( !array_key_exists( 'host', $data ) && $this->host )
			$data['host']	= $this->host;
		if( !array_key_exists( 'userAgent', $data ) && $this->userAgent )
			$data['userAgent']	= $this->userAgent;
		$curl	= new Net_CURL( $this->url.'/record' );
		$curl->setOption( CURLOPT_RETURNTRANSFER, TRUE );
		$curl->setOption( CURLOPT_POST, TRUE );
		$curl->setOption( CURLOPT_POSTFIELDS, $data );
		return $curl->exec();
	}
}
?>
