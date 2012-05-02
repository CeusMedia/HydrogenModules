<?php
class Model_Finance_Fund extends CMF_Hydrogen_Model{

	protected $name		= 'finance_funds';
	protected $columns	= array(
		'fundId',
		'userId',
		'ISIN',
		'currency',
		'pieces',
		'kag',
		'title',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'fundId';
	protected $indices		= array(
		'userId',
		'ISIN',
		'currency',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function  __construct( CMF_Hydrogen_Environment_Abstract $env, $id = NULL ){
		parent::__construct( $env, $id );
	}
}
?>