<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form_Block extends Model
{
	protected array $columns		= [
		'blockId',
		'title',
		'identifier',
		'content',
	];

	protected array $indices		= [
		'identifier',
	];

	protected string $primaryKey	= 'blockId';

	protected string $name			= 'form_blocks';

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Form_Block::class;
}
