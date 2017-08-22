<?php
class Model_Billing_Reserve extends CMF_Hydrogen_Model{
	protected $name		= "billing_reserves";
	protected $columns	= array(
		'reserveId',
		'corporationId',
		'status',
		'personalize',
		'percent',
		'amount',
		'title',
	);
	protected $primaryKey	= 'reserveId';
	protected $indices		= array(
		'corporationId',
		'status',
		'personalize',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
