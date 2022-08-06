<?php

use CeusMedia\HydrogenFramework\Model;

class Model_ArticleTag extends Model
{
	protected $name		= 'article_tags';

	protected $columns	= array(
		'articleTagId',
		'articleId',
		'tagId'
	);

	protected $primaryKey	= 'articleTagId';

	protected $indices		= array(
		'articleId',
		'tagId'
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
