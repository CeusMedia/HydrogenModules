<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Tag extends Model
{
	protected $name		= 'tags';

	protected $columns	= array(
		'tagId',
		'content',
		'createdAt'
	);

	protected $primaryKey	= 'tagId';

	protected $indices		= ['content'];

	protected $fetchMode	= PDO::FETCH_OBJ;
}
