<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Model;

class Model_Customer_Rating extends Model
{
	protected string $name			= 'customer_ratings';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'customerRatingId';

	protected array $indices		= [
		'customerId',
		'userId'
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;

	public function calculateCustomerIndex( $rating ): float
	{
		$factors	= [
			'affability'	=> 3,
			'guidability'	=> 4,
			'growthRate'	=> 5,
			'profitability'	=> 8,
			'paymentMoral'	=> 7,
			'adherence'		=> 1,
			'uptightness'	=> -2,
		];
		$index		= 0;
		$properties	= [];
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
