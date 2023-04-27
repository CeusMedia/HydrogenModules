<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\API\Google\Maps\Geocoder as GoogleMapsGeocoder;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Shop extends Controller
{
	protected HttpRequest $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;

	/**	@var		Logic_ShopManager		$logicShop			Instance of shop logic */
	protected Logic_ShopManager $logicShop;

	/**	@var		Logic_ShopBridge		$logicBridge		Instance of shop bridge logic */
	protected Logic_ShopBridge $logicBridge;


	public function index()
	{
		$orders			= ['orderId' => 'ASC'];

		$ordersTotal	= $this->logicShop->getOrders( ['status' => '>= 2'], $orders );
		$customerIds		= [];
		foreach( $ordersTotal as $order )
			$customerIds[]	= (int) $order->customerId;

		$this->addData( 'ordersNotFinished', $this->logicShop->getOrders( ['status' => [2, 3, 4, 5]], $orders ) );
		$this->addData( 'ordersNotPayed', $this->logicShop->getOrders( ['status' => '2'], $orders ) );
		$this->addData( 'ordersNotDelivered', $this->logicShop->getOrders( ['status' => [3, 4]], $orders ) );
		$this->addData( 'ordersTotal', $ordersTotal );

		$customers	= [];
		if( $customerIds )
			$customers	= $this->logicShop->getCustomers( ['customerId' => $customerIds], ['customerId' => 'DESC'], [10] );

		//  ALTER TABLE `shop_customers` ADD `longitude` FLOAT NULL AFTER `password`, ADD `latitude` FLOAT NULL AFTER `longitude`;
/*		$geocoder	= new GoogleMapsGeocoder( "" );
		$geocoder->setCachePath( 'cache/' );
		$modelCustomer	= new Model_Shop_Customer( $this->env );
		$markers	= [];
		foreach( $customers as $customer ){
			if( !$customer->longitude ){
				try{
					$tags		= $geocoder->getGeoTags( $customer->address.', '.$customer->city.', '.$customer->country );
					$customer->longitude	= $tags['longitude'];
					$customer->latitude		= $tags['latitude'];
					$modelCustomer->edit( $customer->customerId, $tags );
				}
				catch( Exception $e ){}
			}
			if( $customer->longitude )
				$markers[]	= ['lon' => $customer->longitude, 'lat' => $customer->latitude];
		}
*/
		foreach( $customers as $customer )
			$markers[]	= ['lon' => $customer->longitude, 'lat' => $customer->latitude];

		$this->addData( 'markers', $markers );

	}

	public function setTab( $newsletterId, $tabKey )
	{
		$this->session->set( 'manage.shop.tab', $tabKey );
#		$this->restart( './work/newsletter/edit/'.$newsletterId );
	}

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logicShop	= new Logic_ShopManager( $this->env );
		$this->logicBridge	= new Logic_ShopBridge( $this->env );
	}
}
