<?php
class Model_Tag extends CMF_Hydrogen_Model{

	protected $name		= 'tags';
	protected $columns	= array(
		'tagId',
		'content',
		'createdAt'
	);
	protected $primaryKey	= 'tagId';
	protected $indices		= array( 'content' );
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function  __construct( CMF_Hydrogen_Environment_Abstract $env, $id = NULL ){
		parent::__construct( $env, $id );
	}
}
?>