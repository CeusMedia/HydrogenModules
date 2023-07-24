<?php

use CeusMedia\HydrogenFramework\Model;

class Model_ArticleTag extends Model
{
	protected string $name		= 'article_tags';

	protected array $columns	= [
		'articleTagId',
		'articleId',
		'tagId'
	];

	protected string $primaryKey	= 'articleTagId';

	protected array $indices		= [
		'articleId',
		'tagId'
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
