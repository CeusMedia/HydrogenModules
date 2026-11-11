<?php
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Entity;

class Entity_IP_Check_Query extends Entity
{
	public string $query	= '';
	public string $domain	= '';
	public array $config	= [];

	public function __construct( Dictionary|array $data = [] )
	{
		parent::__construct( $data, FALSE );
	}
}