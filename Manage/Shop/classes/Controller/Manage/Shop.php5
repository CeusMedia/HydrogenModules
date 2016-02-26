<?php
class Controller_Manage_Shop extends CMF_Hydrogen_Controller{
	/**	@var		Logic_Shop			$logicShop			Instance of shop logic */
	protected $logicShop;
	/**	@var		Logic_ShopBridge	$logicBridge		Instance of shop bridge logic */
	protected $logicBridge;

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logicShop	= new Logic_Shop( $this->env );
		$this->logicBridge	= new Logic_ShopBridge( $this->env );
	}

	public function index(){


		$orders			= array( 'orderId' => 'ASC' );

		$ordersTotal	= $this->logicShop->getOrders( array( 'status' => '>=2' ), $orders );
		$customerIds		= array();
		foreach( $ordersTotal as $order )
			$customerIds[]	= (int) $order->customerId;

		$this->addData( 'ordersNotFinished', $this->logicShop->getOrders( array( 'status' => array( 2, 3, 4, 5 ) ), $orders ) );
		$this->addData( 'ordersNotPayed', $this->logicShop->getOrders( array( 'status' => '2' ), $orders ) );
		$this->addData( 'ordersNotDelievered', $this->logicShop->getOrders( array( 'status' => array( 3, 4 ) ), $orders ) );
		$this->addData( 'ordersTotal', $ordersTotal );

		$customers	= array();
		if( $customerIds )
			$customers	= $this->logicShop->getCustomers( array( 'customerId' => $customerIds ), array( 'customerId' => 'DESC' ), array( 10 ) );

		//  ALTER TABLE `shop_customers` ADD `longitude` FLOAT NULL AFTER `password`, ADD `latitude` FLOAT NULL AFTER `longitude`;
/*		$geocoder	= new Net_API_Google_Maps_Geocoder( "" );
		$geocoder->setCachePath( 'cache/' );
		$modelCustomer	= new Model_Shop_Customer( $this->env );
		$markers	= array();
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
				$markers[]	= array( 'lon' => $customer->longitude, 'lat' => $customer->latitude );
		}
*/
		foreach( $customers as $customer )
			$markers[]	= array( 'lon' => $customer->longitude, 'lat' => $customer->latitude );

		$this->addData( 'markers', $markers );

	}

	public function setTab( $newsletterId, $tabKey ){
		$this->session->set( 'manage.shop.tab', $tabKey );
#		$this->restart( './work/newsletter/edit/'.$newsletterId );
	}
}
?>
