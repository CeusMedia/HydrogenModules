<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Tag extends Model
{
	protected string $name		= 'tags';

	protected array $columns	= [
		'tagId',
		'title',
		'number'
	];

	protected string $primaryKey	= 'tagId';

	protected array $indices		= [
		'title'
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;

	public function getRelatedTags( $tagId )
	{
		$modelRelation	= new Model_ArticleTag( $this->env );
		$modelArticle	= new Model_Article( $this->env );
		$articles	= $modelRelation->getAllByIndex( 'tagId', $tagId );
		$list		= [];
		foreach( $articles as $articles ){
			$relations	= $modelRelation->getAllByIndex( 'articleId', $articles->articleId );
			foreach( $relations as $relation ){
				if( $relation->tagId == $tagId )
					continue;
				$conditions	= ['articleId' => $relation->articleId, 'status' => 1];
				if( !$modelArticle->getByIndices( $conditions ) )
					continue;
				if( !isset( $list[$relation->tagId] ) )
					$list[$relation->tagId]	= 0;
				$list[$relation->tagId] ++;
			}
		}
		asort( $list );
		$tags	= [];
		foreach( $list as $tagId => $number ){
			$tag	= $this->get( $tagId );
			$tag->relations	= $number;
			$tags[]	= $tag;
		}
		return $tags;
	}
}
