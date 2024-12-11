<?php

class Entity_CSRF_Token
{
	public int|string $tokenId	= 0;
	public int $status			= Logic_CSRF::STATUS_OPEN;

	public string $token;
	public string $sessionId;
	public string $ip;
	public string $formName;
	public int $timestamp;

	public function __construct( array $data )
	{
		$this->timestamp	= time();
		foreach( $data as $key => $value )
			if( property_exists( $this, $key ) )
				$this->$key	= $value;
	}
}