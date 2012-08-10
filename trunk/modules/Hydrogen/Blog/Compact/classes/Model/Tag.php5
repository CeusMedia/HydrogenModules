<?php
class Model_Tag extends CMF_Hydrogen_Model{
	protected $name		= 'tags';
	protected $columns	= array(
		'tagId',
		'title',
		'number'
	);
	protected $primaryKey	= 'tagId';
	protected $indices		= array(
		'title'
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function getRelatedTags( $tagId ){
		$modelRelation	= new Model_ArticleTag( $this->env );
		$modelArticle	= new Model_Article( $this->env );
		$articles	= $modelRelation->getAllByIndex( 'tagId', $tagId );
		$list		= array();
		foreach( $articles as $articles ){
			$relations	= $modelRelation->getAllByIndex( 'articleId', $articles->articleId );
			foreach( $relations as $relation ){
				if( $relation->tagId == $tagId )
					continue;
				$conditions	= array( 'articleId' => $relation->articleId, 'status' => 1 );
				if( !$modelArticle->getByIndices( $conditions ) )
					continue;
				if( !isset( $list[$relation->tagId] ) )
					$list[$relation->tagId]	= 0;
				$list[$relation->tagId] ++;
			}
		}
		asort( $list );
		$tags	= array();
		foreach( $list as $tagId => $number ){
			$tag	= $this->get( $tagId );
			$tag->relations	= $number;
			$tags[]	= $tag;
		}
		return $tags;
	}
}
?>
