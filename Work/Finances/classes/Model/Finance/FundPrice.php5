<?php
class Model_Finance_FundPrice extends CMF_Hydrogen_Model{

	protected $name		= 'finance_fund_prices';
	protected $columns	= array(
		'fundPriceId',
		'fundId',
		'pieces',
		'price',
		'timestamp',
	);
	protected $primaryKey	= 'fundPriceId';
	protected $indices		= array( 'fundId' );
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function  __construct( CMF_Hydrogen_Environment_Abstract $env, $id = NULL ){
		parent::__construct( $env, $id );
	}
}
?>
