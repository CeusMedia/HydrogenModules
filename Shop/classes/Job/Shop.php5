<?php
class Job_Shop extends Job_Abstract{

	public function migrate(){
		$modelCustomerNew	= new Model_Shop_Customer( $this->env );
		$modelCustomerOld	= new Model_Shop_CustomerOld( $this->env );
		$modelAddress		= new Model_Address( $this->env );
		$modelOrders		= new Model_Shop_Order( $this->env );
		$pathLocales		= $this->env->getConfig()->get( 'path.locales' );
//		$modelOrders->getAll()
		$conditions	= array();
		$orders		= array( 'customerId' => 'ASC' );
		$limit		= array( 0, 1000 );
		$countries	= FS_File_INI_Reader::load( $pathLocales.'de/countries.ini' );
		$customers	= $modelCustomerOld->getAll( $conditions, $orders/*, $limit*/ );
//		remark( 'Found: '.count( $customers ) );
		$count		= 0;
		foreach( $customers as $customer ){
			$order	= $modelOrders->getByIndex( 'customerId', $customer->customerId );
			if( $order ){
				$country	= 'DE';
				if( $customer->country == 34 )
					$customer->country	= "DE";
				if( $customer->country == 126 )
					$customer->country	= "AT";
				if( $customer->country == 45 )
					$customer->country	= "PT";
				if( array_key_exists( $customer->country, $countries ) )
					$country	= $countries[$customer->country];
				$modelAddress->add( array(
					'relationId'	=> $customer->customerId,
					'relationType'	=> 'customer',
					'type'			=> Model_Address::TYPE_DELIVERY,
					'country'		=> $country,
					'region'		=> $customer->region,
					'city'			=> $customer->city,
					'postcode'		=> $customer->postcode,
					'street'		=> $customer->address,
					'phone'			=> $customer->phone,
					'email'			=> $customer->email,
					'firstname'		=> $customer->firstname,
					'surname'		=> $customer->lastname,
					'institution'	=> $customer->institution,
					'createdAt'		=> $order->createdAt,
					'modifiedAt'	=> $order->createdAt,
				) );
				if( (int) $customer->alternative > 0 ){
					$country	= 'DE';
					if( $customer->billing_country == 34 )
						$customer->billing_country	= "DE";
					if( $customer->billing_country == 126 )
						$customer->billing_country	= "AT";
					if( $customer->billing_country == 45 )
						$customer->billing_country	= "PT";
					if( array_key_exists( $customer->billing_country, $countries ) )
						$country	= $countries[$customer->billing_country];
					$modelAddress->add( array(
						'relationId'	=> $customer->customerId,
						'relationType'	=> 'customer',
						'type'			=> Model_Address::TYPE_BILLING,
						'country'		=> $country,
						'city'			=> $customer->billing_city,
						'postcode'		=> $customer->billing_postcode,
						'street'		=> $customer->billing_address,
						'phone'			=> $customer->billing_phone,
						'email'			=> $customer->billing_email,
						'firstname'		=> $customer->billing_firstname,
						'createdAt'		=> $order->createdAt,
						'modifiedAt'	=> $order->createdAt,
					) );
				}
				$modelCustomerNew->add( array( 'customerId' => $customer->customerId ) );
				$modelCustomerOld->remove( $customer->customerId );
				$this->showProgress( ++$count, count( $customers ) );

//				$this->out( 'Imported customer '.$customer->customerId );
			}
		}
	}
}
