<?php

use CeusMedia\HydrogenFramework\Model;

class Model_ArticleAuthor extends Model
{
	protected string $name		= 'article_authors';

	protected array $columns	= array(
		'articleAuthorId',
		'articleId',
		'userId'
	);

	protected string $primaryKey	= 'articleAuthorId';

	protected array $indices		= array(
		'articleId',
		'userId'
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
