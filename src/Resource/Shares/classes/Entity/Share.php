<?php
declare(strict_types=1);

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\ID;
use CeusMedia\HydrogenFramework\Entity;

class Entity_Share extends Entity
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

	public function __construct( Dictionary|array $data = [] )
	{
		$this->uuid			= ID::uuid();
		$this->createdAt	= time();
		parent::__construct( $data );
	}
}