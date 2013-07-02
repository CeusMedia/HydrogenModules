<?php
class Model_Gallery extends CMF_Hydrogen_Model{
	protected $name		= 'galleries';
	protected $columns	= array(
		'galleryId',
		'parentId',
		'status',
		'folder',
		'title',
		'content',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'galleryId';
	protected $indices	= array(
		'status',
		'folder',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function getGalleryAuthors( $galleryId ){
		$model	= new Model_GalleryAuthor( $this->env );
		$ids	= $model->getAllByIndex( 'galleryId', $galleryId );
		$model	= new Model_User( $this->env );
		$users	= array();
		foreach( $ids as $relation )
			$users[]	= $model->get( $relation->userId );
		return $users;
	}

	public function getGalleryTags( $galleryId ){
		$model	= new Model_GalleryTag( $this->env );
		$ids	= $model->getAllByIndex( 'galleryId', $galleryId );
		$model	= new Model_Tag( $this->env );
		$tags	= array();
		foreach( $ids as $relation ){
			$tag	= $model->get( $relation->tagId );
			$tags[$tag->title]	= $tag;
		}
		ksort( $tags );
		return $tags;
	}
}
?>
