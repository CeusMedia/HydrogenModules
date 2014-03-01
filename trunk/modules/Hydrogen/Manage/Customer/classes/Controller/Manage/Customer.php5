<?php
class Controller_Manage_Customer extends CMF_Hydrogen_Controller{

	protected $messenger;
	protected $modelCustomer;
	protected $modelRating;

	public function __onInit(){
		$this->messenger		= $this->env->getMessenger();
		$this->modelCustomer	= new Model_Customer( $this->env );
		$this->addData( 'useRatings', $this->env->getModules()->has( 'Manage_Customer_Rating' ) );
		$this->addData( 'useMap', $this->env->getModules()->has( 'UI_Map' ) );
	}

	public static function ___registerHints( $env, $context, $module, $arguments = NULL ){
		if( class_exists( 'View_Helper_Hint' ) )
			View_Helper_Hint::registerHintsFromModuleHook( $env, $module );
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
				$rating->index		= $this->modelRating->calculateCustomerIndex( $rating );
			}
			$customer->rating	= $rating;
		}
		$this->addData( 'customers', $customers );
	}

	public function map( $customerId ){
		$customer	= $this->modelCustomer->get( $customerId );
		if( !$customer ){
			$this->messenger->noteError( 'Invalid customer' );
			$this->restart( './manage/customer' );
		}
		$this->addData( 'customerId', $customerId );
		$this->addData( 'customer', $customer );
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
