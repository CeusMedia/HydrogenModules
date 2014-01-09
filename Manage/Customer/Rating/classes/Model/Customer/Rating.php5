<?php
class Model_Customer_Rating extends CMF_Hydrogen_Model{
	protected $name			= 'customer_ratings';
	protected $columns		= array(
		'customerRatingId',
		'customerId',
		'userId',
		'affability',				//  Umgänglichkeit
		'guidability',				//  Beratungspotenzial, Beratbarkeit
		'growthRate',				//  Wachstumschancen
		'profitability',			//  Rentabilität, Wirtschaftlichkeit
		'paymentMoral',				//  Zahlungsmoral
		'adherence',				//  Termintreue
		'uptightness',				//  Nervfaktor, Nervosität, Verspanntheit
		'comment',
		'timestamp',
	);
	protected $primaryKey	= 'customerRatingId';
	protected $indices		= array( 'customerId', 'userId' );
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
