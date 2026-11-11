<?php
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Entity;

class Entity_IP_Check_Source extends Entity
{
	public string $description	= '';
	public bool $listed			= FALSE;

	public function __construct( Dictionary|array $data = [] )
	{
		parent::__construct( $data, FALSE );
	}
}