<?php
class Model_ArticleAuthor extends CMF_Hydrogen_Model{
	protected $name		= 'article_authors';
	protected $columns	= array(
		'articleAuthorId',
		'articleId',
		'userId'
	);
	protected $primaryKey	= 'articleAuthorId';
	protected $indices		= array(
		'articleId',
		'userId'
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
