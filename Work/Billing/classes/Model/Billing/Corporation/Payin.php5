<?php
class Model_Billing_Corporation_Payin extends CMF_Hydrogen_Model{

	const STATUS_NEW	= 0;
	const STATUS_BOOKED	= 1;

	protected $name		= "billing_corporation_payins";
	protected $columns	= array(
		'corporationPayinId',
		'corporationId',
		'status',
		'amount',
		'title',
		'dateBooked',
	);
	protected $primaryKey	= 'corporationPayinId';
	protected $indices		= array(
		'corporationId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
