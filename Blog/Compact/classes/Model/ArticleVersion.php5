<?php
class Model_ArticleVersion extends CMF_Hydrogen_Model{
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
?>