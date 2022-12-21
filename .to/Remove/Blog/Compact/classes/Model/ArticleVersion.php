<?php

use CeusMedia\HydrogenFramework\Model;

class Model_ArticleVersion extends Model
{
	protected string $name		= 'article_versions';

	protected array $columns	= array(
		'articleVersionId',
		'articleId',
		'title',
		'content',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'articleVersionId';

	protected array $indices		= array(
		'articleId',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
