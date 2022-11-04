<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Gallery extends Model
{
	protected string $name		= 'galleries';

	protected array $columns	= array(
		'galleryId',
		'parentId',
		'status',
		'folder',
		'title',
		'content',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'galleryId';

	protected array $indices	= array(
		'status',
		'folder',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;

	public function getGalleryAuthors( $galleryId )
	{
		$model	= new Model_GalleryAuthor( $this->env );
		$ids	= $model->getAllByIndex( 'galleryId', $galleryId );
		$model	= new Model_User( $this->env );
		$users	= [];
		foreach( $ids as $relation )
			$users[]	= $model->get( $relation->userId );
		return $users;
	}

	public function getGalleryTags( $galleryId )
	{
		$model	= new Model_GalleryTag( $this->env );
		$ids	= $model->getAllByIndex( 'galleryId', $galleryId );
		$model	= new Model_Tag( $this->env );
		$tags	= [];
		foreach( $ids as $relation ){
			$tag	= $model->get( $relation->tagId );
			$tags[$tag->title]	= $tag;
		}
		ksort( $tags );
		return $tags;
	}
}
