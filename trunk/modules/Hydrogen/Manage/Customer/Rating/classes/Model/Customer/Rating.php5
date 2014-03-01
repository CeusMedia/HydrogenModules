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


	public function calculateCustomerIndex( $rating ){
		$factors	= array(
			'affability'	=> 3,
			'guidability'	=> 4,
			'growthRate'	=> 5,
			'profitability'	=> 8,
			'paymentMoral'	=> 7,
			'adherence'		=> 1,
			'uptightness'	=> -2,
		);
		$index		= 0;
		$properties	= array();
		foreach( $factors as $property => $factor ){
			if( $rating->$property <= 0 )
				continue;
			if( $factor < 0 )
				$index	+= abs( $factor ) * ( 5 - $rating->$property );
			else
				$index	+= $factor * ( $rating->$property - 1 );
			$properties[]	= abs( $factor );
		}
		$sum	= array_sum( $properties );
		return ( $index / $sum ) + 1;
	}
}
?>
