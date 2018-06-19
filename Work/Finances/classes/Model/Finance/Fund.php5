<?php
class Model_Finance_Fund extends CMF_Hydrogen_Model{

	protected $name		= 'finance_funds';
	protected $columns	= array(
		'fundId',
		'userId',
		'type',
		'scope',
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
		'type',
		'scope',
		'ISIN',
		'currency',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function  __construct( CMF_Hydrogen_Environment $env, $id = NULL ){
		parent::__construct( $env, $id );
	}
}
?>
