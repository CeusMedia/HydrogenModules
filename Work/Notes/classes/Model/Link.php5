<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Link extends Model
{
	protected $name		= 'links';

	protected $columns	= array(
		'linkId',
		'url',
		'createdAt',
		'lastAssignAt',
		'lastSearchAt',
	);

	protected $primaryKey	= 'linkId';

	protected $indices		= array(
		'url'
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
