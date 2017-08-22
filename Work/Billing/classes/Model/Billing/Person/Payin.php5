<?php
class Model_Billing_Person_Payin extends CMF_Hydrogen_Model{

	const STATUS_NEW	= 0;
	const STATUS_BOOKED	= 1;

	protected $name		= "billing_person_payins";
	protected $columns	= array(
		'personPayinId',
		'personId',
		'status',
		'amount',
		'title',
		'dateBooked',
	);
	protected $primaryKey	= 'personPayinId';
	protected $indices		= array(
		'personId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
