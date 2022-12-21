<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Article extends Model
{
	protected string $name		= 'articles';

	protected array $columns	= array(
		'articleId',
//		'authorId',
		'status',
		'title',
		'content',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'articleId';

	protected array $indices	= array(
//		'authorId',
		'status',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;

	public function getArticleAuthors( $articleId )
	{
		$model	= new Model_ArticleAuthor( $this->env );
		$ids	= $model->getAllByIndex( 'articleId', $articleId );
		$model	= new Model_User( $this->env );
		$users	= [];
		foreach( $ids as $relation )
			$users[]	= $model->get( $relation->userId );
		return $users;
	}

	public function getArticleTags( $articleId )
	{
		$model	= new Model_ArticleTag( $this->env );
		$ids	= $model->getAllByIndex( 'articleId', $articleId );
		$model	= new Model_Tag( $this->env );
		$tags	= [];
		foreach( $ids as $relation ){
			$tag	= $model->get( $relation->tagId );
			$tags[$tag->title]	= $tag;
		}
		ksort( $tags );
		return $tags;
	}

	public function getArticleVersions( $articleId )
	{
		$model	= new Model_ArticleVersion( $this->env );
		return $model->getAll(
			array( 'articleId'			=> $articleId ),
			array( 'articleVersionId'	=> 'ASC' )
		);
	}
}
