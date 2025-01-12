<?php

use CeusMedia\Common\Alg\ID;

class Entity_Share
{
	public int $shareId				= 0;
	public int $status				= Model_Share::STATUS_INACTIVE;
	public int $access				= Model_Share::ACCESS_PUBLIC;
	public int $validity			= 0;
	public string $moduleId			= '';
	public int|string $relationId	= 0;
	public string $path;
	public string $uuid;
	public int $createdAt;
	public int $accessedAt;

	public ?Entity_File $qr			= NULL;

	public static function fromArray( array $data ): static
	{
		$className	= static::class;
		$instance	= new $className();
		foreach( $data as $key => $value )
			if( property_exists( $instance, $key ) )
				$instance->{$key} = $value;
		return $instance;
	}

	public function __construct()
	{
		$this->uuid			= ID::uuid();
		$this->createdAt	= time();
	}
}