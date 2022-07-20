<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Relocation extends Model
{
	protected $name		= 'relocations';

	protected $columns	= array(
		"relocationId",
		"creatorId",
		"status",
		"title",
		"url",
		"views",
		"createdAt",
		"usedAt",
	);

	protected $primaryKey	= 'relocationId';

	protected $indices		= array(
		"status",
		"url",
		"createdAt",
		"usedAt",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
