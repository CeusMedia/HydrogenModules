<?php

use CeusMedia\HydrogenFramework\Model;

class Model_ArticleTag extends Model
{
	protected string $name		= 'article_tags';

	protected array $columns	= array(
		'articleTagId',
		'articleId',
		'tagId'
	);

	protected string $primaryKey	= 'articleTagId';

	protected array $indices		= array(
		'articleId',
		'tagId'
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
