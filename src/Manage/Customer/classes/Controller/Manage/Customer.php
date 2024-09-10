<?php

use CeusMedia\Common\Net\API\Google\Maps\Geocoder as GoogleMapsGeocoder;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Customer extends Controller
{
	protected MessengerResource $messenger;
	protected Model_Customer $modelCustomer;
	protected Model_Customer_Rating $modelRating;

	public static function ___registerHints( Environment $env, $context, $module, $arguments = NULL ): void
	{
		if( class_exists( 'View_Helper_Hint' ) )
			View_Helper_Hint::registerHintsFromModuleHook( $env, $module );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		$request		= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$data	= $request->getAll();
			$data['userId']		= (int) $this->env->getSession()->get( 'auth_user_id' );
			$data['createdAt']	= time();
			$customerId			= $this->modelCustomer->add( $data );
			$this->env->getMessenger()->noteSuccess( 'Customer has been saved.' );
			if( !$this->resolveGeocode( $customerId ) )
				$this->messenger->noteNotice( 'Für diese Adresse konnte keine Geokoordinaten ermittelt werden.' );
			$this->restart( NULL, TRUE );
		}
		$customer	= [];
		foreach( $this->modelCustomer->getColumns() as $key )
			$customer[$key]	= $request->get( $key );
		$this->addData( 'customer', (object) $customer );
	}

	/**
	 *	@param		int|string		$customerId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $customerId ): void
	{
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

	public function index(): void
	{
		$customers		=  $this->modelCustomer->getAll();
		foreach( $customers as $customer ){
			$order		= ['timestamp' => 'DESC'];
			$limit		= [0, 1];
			$rating		= $this->modelRating->getAllByIndex( 'customerId', $customer->customerId, $order, $limit );
			if( $rating ){
				$rating	= array_pop( $rating );
				$rating->index		= $this->modelRating->calculateCustomerIndex( $rating );
			}
			$customer->rating	= $rating;
		}
		$this->addData( 'customers', $customers );
	}

	/**
	 *	@param		int|string		$customerId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function map( int|string $customerId ): void
	{
		$customer	= $this->modelCustomer->get( $customerId );
		if( !$customer ){
			$this->messenger->noteError( 'Invalid customer' );
			$this->restart( './manage/customer' );
		}
		$this->addData( 'customerId', $customerId );
		$this->addData( 'customer', $customer );
	}

	/**
	 *	@param		int|string		$customerId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function rate( int|string $customerId ): void
	{
		$request		= $this->env->getRequest();

		$customer	= $this->modelCustomer->get( $customerId );
		if( !$customer ){
			$this->messenger->noteError( 'Invalid customer' );
			$this->restart( './manage/customer' );
		}

		if( $request->has( 'save' ) ){
			$data	= [
				'affability'	=> $request->get( 'affability' ) >= 1 ? min( 5, $request->get( 'affability' ) ) : 0,
				'guidability'	=> $request->get( 'guidability' ) >= 1 ? min( 5, $request->get( 'guidability' ) ) : 0,
				'growthRate'	=> $request->get( 'growthRate' ) >= 1 ? min( 5, $request->get( 'growthRate' ) ) : 0,
				'profitability'	=> $request->get( 'profitability' ) >= 1 ? min( 5, $request->get( 'profitability' ) ) : 0,
				'paymentMoral'	=> $request->get( 'paymentMoral' ) >= 1 ? min( 5, $request->get( 'paymentMoral' ) ) : 0,
				'adherence'		=> $request->get( 'adherence' ) >= 1 ? min( 5, $request->get( 'adherence' ) ) : 0,
				'uptightness'	=> $request->get( 'uptightness' ) >= 1 ? min( 5, $request->get( 'uptightness' ) ) : 0,
				'customerId'	=> $customerId,
				'timestamp'		=> time()
			];
			$this->modelRating->add( $data );
			$this->env->getMessenger()->noteSuccess( 'Rating has been saved.' );
			$this->restart( NULL, TRUE );
		}

		$this->addData( 'customer', $customer );
		$this->addData( 'customerId', $customerId );
	}

	/**
	 *	@param		int|string		$customerId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function resolveGeocode( int|string $customerId ): bool
	{
		$customer	= $this->modelCustomer->get( $customerId );
		if( !$customer ){
			$this->messenger->noteError( 'Invalid customer' );
			$this->restart( './manage/customer' );
		}
		try{
			$address	= $customer->city.', '.$customer->street.' '.$customer->nr;
			$apiKey		= $this->env->getModules()->get( 'UI_Map' )->config['apiKey']->value;
			$geocoder	= new GoogleMapsGeocoder( $apiKey );
			$tags		= $geocoder->getGeoTags( $address );
			$this->modelCustomer->edit( $customerId, $tags );
		}
		catch( Exception $e ){
			return FALSE;
		}
		return TRUE;
	}

	protected function __onInit(): void
	{
		$this->messenger		= $this->env->getMessenger();
		$this->modelCustomer	= new Model_Customer( $this->env );
		if( $this->env->getModules()->has( 'Manage_Customer_Rating' ) ){
			$this->modelRating	= new Model_Customer_Rating( $this->env );
		}
		$this->addData( 'useMap', $this->env->getModules()->has( 'UI_Map' ) );
		$this->addData( 'useRatings', $this->env->getModules()->has( 'Manage_Customer_Rating' ) );
		$this->addData( 'useProjects', TRUE );#$this->env->getModules()->has( 'Manage_Customer_Project' ) );
	}
}
