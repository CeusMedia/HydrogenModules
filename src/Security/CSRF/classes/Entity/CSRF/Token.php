<?php
declare(strict_types=1);

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Entity;

class Entity_CSRF_Token extends Entity
{
	public int|string $tokenId	= 0;
	public int $status			= Logic_CSRF::STATUS_OPEN;

	public string $token;
	public string $sessionId;
	public string $ip;
	public string $formName;
	public int $timestamp;

	public function __construct( Dictionary|array $data = [] )
	{
		$this->timestamp	= time();
		parent::__construct( $data );
	}
}