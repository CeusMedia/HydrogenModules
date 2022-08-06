<?php

use CeusMedia\HydrogenFramework\Model;

class Model_ArticleVersion extends Model
{
	protected $name		= 'article_versions';

	protected $columns	= array(
		'articleVersionId',
		'articleId',
		'title',
		'content',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'articleVersionId';

	protected $indices		= array(
		'articleId',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
