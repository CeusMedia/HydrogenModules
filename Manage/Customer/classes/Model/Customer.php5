<?php
class Model_Customer extends CMF_Hydrogen_Model{
	protected $name			= 'customers';
	protected $columns		= array(
		'customerId',
		'title',
		'affability',				//  Umgänglichkeit
		'guidability',				//  Beratungspotenzial, Beratbarkeit
		'growthRate',				//  Wachstumschancen
		'profitability',			//  Rentabilität, Wirtschaftlichkeit
		'paymentMoral',				//  Zahlungsmoral
		'adherence',				//  Termintreue
		'uptightness',				//  Nervfaktor, Nervosität, Verspanntheit
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'customerId';
	protected $indizes		= array();
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>