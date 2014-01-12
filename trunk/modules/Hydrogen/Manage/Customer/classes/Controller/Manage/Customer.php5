<?php
class Controller_Manage_Customer extends CMF_Hydrogen_Controller{

	protected $messenger;
	protected $modelCustomer;
	protected $modelRating;

	public function __onInit(){
		$this->messenger		= $this->env->getMessenger();
		$this->modelCustomer	= new Model_Customer( $this->env );
		$this->modelRating		= new Model_Customer_Rating( $this->env );
	}
	
	public function add(){
		$request		= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$data	= $request->getAll();
			$data['userId']		= (int) $this->env->getSession()->get( 'userId' );
			$data['createdAt']	= time();
			$customerId			= $this->modelCustomer->add( $data );
			$this->env->getMessenger()->noteSuccess( 'Customer has been saved.' );
			if( !$this->resolveGeocode( $customerId ) )
				$this->messenger->noteNotice( 'Für diese Adresse konnte keine Geokoordinaten ermittelt werden.' );
			$this->restart( NULL, TRUE );
		}
		$customer	= array();
		foreach( $this->modelCustomer->getColumns() as $key )
			$customer[$key]	= $request->get( $key );
		$this->addData( 'customer', (object) $customer );
	}

	protected function calculateCustomerIndex( $rating ){
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
/*		remark( 'index: '.$index );
		remark( 'sum: '.$sum );
		remark( 'props: '.count( $properties ) );
		remark( '~: '.round( $index / $sum, 1 ) );
		die;
*/
		return round( 4 - ( $index / $sum ) + 1, 1 );
		return round( $index / $sum, 1 );
		return round( ( $index / 7 + 10 / 7 ) / ( 21.4 / 5 ), 1 );
		//  recommend: 4
	}

	public function edit( $customerId ){
		$request	= $this->env->getRequest();
		$customer	= $this->modelCustomer->get( $customerId );
		if( !$customer ){
			$this->messenger->noteError( 'Invalid customer' );
			$this->restart( './manage/customer' );
		}

		if( $request->has( 'save' ) ){
			$data	= $request->getAll();
			$data['modifiedAt']	= time();
			$this->modelCustomer->edit( $customerId, $data );
			$this->messenger->noteSuccess( 'Gespeichert.' );
			$addressOld	= $customer->city.', '.$customer->street.' '.$customer->nr;
			$addressNew	= $data['city'].', '.$data['street'].' '.$data['nr'];
			if( $addressOld !== $addressNew || !($customer->longitude.$customer->latitude) ){
				if( !$this->resolveGeocode( $customerId ) )
					$this->messenger->noteNotice( 'Für diese Adresse konnte keine Geokoordinaten ermittelt werden.' );
			}
			$this->restart( './manage/customer/edit/'.$customerId );
		}

		if( $this->env->getModules()->has( 'Manage_Customer_Rating' ) ){
			$order		= array( 'timestamp' => 'DESC' );
			$limit		= array( 0, 10 );
			$ratings	= $this->modelRating->getAllByIndex( 'customerId', $customerId, $order, $limit );
			$ratings	= array_reverse( $ratings );
			$lastIndex	= NULL;
			$totalIndex	= 0;
			$variance	= 0;
			foreach( $ratings as $nr => $rating ){
				$rating->index		= $this->calculateCustomerIndex( $rating );
				if( !is_null( $lastIndex ) )
					$variance			+= abs( $lastIndex - $rating->index );
				$lastIndex	= $rating->index;
				$totalIndex	+= $rating->index;
			}	
			$customer->ratings	= $ratings;
			$customer->index	= $ratings ? $totalIndex / count( $ratings ) : NULL;
			$customer->variance	= count( $ratings ) > 1 ? $variance / ( count( $ratings ) - 1 ) : NULL;
			$this->addData( 'useRatings', TRUE );
		}
		else
			$this->addData( 'useRatings', FALSE );

		$this->addData( 'customerId', $customerId );
		$this->addData( 'customer', $customer );
	}

	public function index(){
		$customers		=  $this->modelCustomer->getAll();
		foreach( $customers as $nr => $customer ){
			$order		= array( 'timestamp' => 'DESC' );
			$limit		= array( 0, 1 );
			$rating		= $this->modelRating->getAllByIndex( 'customerId', $customer->customerId, $order, $limit );
			if( $rating ){
				$rating	= array_pop( $rating );
				$rating->index		= $this->calculateCustomerIndex( $rating );
			}
			$customer->rating	= $rating;
		}
		$this->addData( 'customers', $customers );
	}

	public function rate( $customerId ){
		$request		= $this->env->getRequest();

		$customer	= $this->modelCustomer->get( $customerId );
		if( !$customer ){
			$this->messenger->noteError( 'Invalid customer' );
			$this->restart( './manage/customer' );
		}
		
		if( $request->has( 'save' ) ){
			$data	= array(
				'affability'	=> $request->get( 'affability' ) >= 1 ? min( 5, $request->get( 'affability' ) ) : 0,
				'guidability'	=> $request->get( 'guidability' ) >= 1 ? min( 5, $request->get( 'guidability' ) ) : 0,
				'growthRate'	=> $request->get( 'growthRate' ) >= 1 ? min( 5, $request->get( 'growthRate' ) ) : 0,
				'profitability'	=> $request->get( 'profitability' ) >= 1 ? min( 5, $request->get( 'profitability' ) ) : 0,
				'paymentMoral'	=> $request->get( 'paymentMoral' ) >= 1 ? min( 5, $request->get( 'paymentMoral' ) ) : 0,
				'adherence'		=> $request->get( 'adherence' ) >= 1 ? min( 5, $request->get( 'adherence' ) ) : 0,
				'uptightness'	=> $request->get( 'uptightness' ) >= 1 ? min( 5, $request->get( 'uptightness' ) ) : 0,
				'customerId'	=> $customerId,
				'timestamp'		=> time()
			);
			$this->modelRating->add( $data );
			$this->env->getMessenger()->noteSuccess( 'Rating has been saved.' );
			$this->restart( NULL, TRUE );
		}

		$this->addData( 'customer', $customer );
		$this->addData( 'customerId', $customerId );
	}

	public function resolveGeocode( $customerId ){
		$customer	= $this->modelCustomer->get( $customerId );
		if( !$customer ){
			$this->messenger->noteError( 'Invalid customer' );
			$this->restart( './manage/customer' );
		}
		try{
			$address	= $customer->city.', '.$customer->street.' '.$customer->nr;
			$geocoder	= new Net_API_Google_Maps_Geocoder();
			$tags		= $geocoder->getGeoTags( $address );
			$this->modelCustomer->edit( $customerId, $tags );
		}
		catch( Exception $e ){
			return FALSE;
		}
		return TRUE;
	}
}
?>
