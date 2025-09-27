<?php
class Job_Work_Meeting extends Job_Abstract
{
	public function remind()
	{
		$model	= new Model_Work_Meeting( $this->env );

		//  STRATEGY 1: PDO query mode with date operations in query.
		/** @var Resource_Database $dbc */
		$dbc	= $this->env->getDatabase();
		$date	= date( 'Y-m-d H:i:s' );
		$query	= 'SELECT * FROM '.$model->getName()." WHERE at BETWEEN '".$date."' AND DATE_ADD('".$date."', INTERVAL 1 HOUR)";
		$dbc->getConnection()->exec( $query );



		$orders	= [];
		$limits	= [];

		//  STRATEGY 2: Table model query mode with exact target time, MUST run every minute.
		$date	= DateTime::createFromFormat( 'Y-m-d H:i:s', $date );
		$target	= $date->add( new DateInterval( 'PT1H' ) );
		$conditions	= [
			'date'	=> $target->format('Y-m-d H:i' ).':00'
		];
		$model->getAll( $conditions, $orders, $limits );

		//  STRATEGY 3: Table model query mode with AND (EXPERIMENTAL)
		$date	= DateTime::createFromFormat( 'Y-m-d H:i:s', $date );
		$before	= $date->add( new DateInterval( 'PT1H' ) )->format('Y-m-d H:i:s' );
		$conditions	= [
			'date'	=> ['> '.$before, '< '.$date]
		];
		$items	= $model->getAll( $conditions, $orders, $limits );
		$this->out( 'Found '.count( $items ) );

	}
}