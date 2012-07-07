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
		$model		= new Model_ArticleTag( $this->env );
		$articles	= $model->getAllByIndex( 'tagId', $tagId );
		foreach( $articles as $articles ){
			$relations	= $model->getAllByIndex( 'articleId', $articles->articleId );
			foreach( $relations as $relation ){
				if( $relation->tagId == $tagId )
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
