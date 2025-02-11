<?php
declare(strict_types=1);

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Entity;

class Entity_File extends Entity
{
	public int $fileId			= 0;
	public int $creatorId		= 0;
	public ?string $moduleId	= NULL;
	public string $hash;
	public string $mimeType;
	public int $fileSize		= 0;
	public string $filePath;
	public string $fileName;
	public int $createdAt;
	public int $modifiedAt		= 0;
	public int $viewedAt		= 0;
	public int $viewCount		= 0;

	public ?string $realPath	= NULL;

	public ?string $content		= NULL;

	public static function fromArray( array $data ): static
	{
		$className	= static::class;
		$instance	= new $className();
		foreach( $data as $key => $value )
			if( property_exists( $instance, $key ) )
				$instance->{$key} = $value;
		return $instance;
	}

	public function __construct( Dictionary|array $data = [] )
	{
		$this->createdAt = time();
		parent::__construct( $data );
	}
}
