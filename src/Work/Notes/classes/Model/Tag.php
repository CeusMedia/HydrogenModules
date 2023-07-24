<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Tag extends Model
{
	protected string $name			= 'tags';

	protected array $columns		= [
		'tagId',
		'content',
		'createdAt'
	];

	protected string $primaryKey	= 'tagId';

	protected array $indices		= [
		'content'
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
