<?php
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Entity;

class Entity_IP_Check_Result extends Entity
{
	public string $ip			= '';
	public int $ipVersion		= 4;
	public bool $checked		= FALSE;
	public bool $clean			= FALSE;
	public int $score			= 0;
	public array $sources		= [];

	public function __construct( Dictionary|array $data = [] )
	{
		parent::__construct( $data, FALSE );
	}
}