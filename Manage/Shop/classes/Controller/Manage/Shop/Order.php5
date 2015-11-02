<?php
class Controller_Manage_Shop_Order extends Controller_Manage_Shop{

	/**	@var		Logic_Shop			$logicShop			Instance of shop logic */
	protected $logicShop;
	/**	@var		Logic_ShopBridge	$logicBridge		Instance of shop bridge logic */
	protected $logicBridge;

	protected function __onInit(){
		parent::__onInit();
		$this->logicShop	= new Logic_Shop( $this->env );
		$this->logicBridge	= new Logic_ShopBridge( $this->env );

		$sessionPrefix		= 'module.manage_shop_order.filter.';
		if( !$this->session->get( $sessionPrefix.'order' ) )
				$this->session->set( $sessionPrefix.'order', 'createdAt:DESC' );
		if( !$this->session->get( $sessionPrefix.'status' ) )
				$this->session->set( $sessionPrefix.'status', array( -5, 2, 3, 4, 5 ) );
	}

	public function edit( $orderId ){
		$order	= $this->logicShop->getOrder( $orderId, TRUE );
		if( !$order ){
			$this->env->getMessenger()->noteError( 'Invalid order ID.' );
			$this->restart( NULL, TRUE );
		}
		foreach( $order->positions as $nr => $position ){
			$bridgeId	= (int) $position->bridgeId;
			$bridge		= $this->logicBridge->getBridgeObject( $position->bridgeId );
			$order->positions[$nr]->bridge	= $this->logicBridge->getBridge( $position->bridgeId );
			if( $bridge->check( $position->articleId, FALSE ) ){
				$order->positions[$nr]->article	= $this->logicBridge->getArticle( $bridgeId, $position->articleId );
			}
			else{
				$this->messenger->noteNotice( "This order contains articles, which has been removed from catalog. Therefore these articles are not shown here." );
				unset( $order->positions[$nr] );
			}
		}
		$this->addData( 'order', $order );
	}

	public function filter( $reset = FALSE ){
		$sessionPrefix	= 'module.manage_shop_order.filter.';
		$this->session->set( $sessionPrefix.'customer', trim( $this->request->get( 'customer' ) ) );
		$this->session->set( $sessionPrefix.'status', $this->request->get( 'status' ) );
		$this->session->set( $sessionPrefix.'order', $this->request->get( 'order' ) );
		if( $reset ){
 			$this->session->remove( $sessionPrefix.'customer' );
			$this->session->remove( $sessionPrefix.'status' );
			$this->session->remove( $sessionPrefix.'order' );
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $pageNr = 0 ){
		$filters	= $this->session->getAll( 'module.manage_shop_order.filter.' );
		$orders		= array();
		$conditions	= array();
		foreach( $filters as $filterKey => $filterValue ){
			switch( $filterKey ){
				case 'customer':
					if( strlen( trim( $filterValue ) ) ){
						$model		= new Model_Shop_Customer( $this->env );
						$value		= '%'.str_replace( " ", "%", str_replace( ' ', '', $filterValue ) ).'%';
						$find		= array( 'CONCAT(firstname, lastname)' => $value );
						if( ( $customers = $model->getAll( $find ) ) ){
							$conditions['customerId']	= array();
							foreach( $customers as $customer )
								$conditions['customerId'][]	= $customer->customerId;
						}
					}
					break;
				case 'status':
					$conditions['status']	= $filterValue;
					break;
				case 'order':
					$parts		= explode( ":", $filterValue );
					$orders[$parts[0]]	= strtoupper( $parts[1] );
					break;
			}
		}

		$orders			= $this->logicShop->getOrders( $conditions, $orders, array( $pageNr * 15, 15 ) );
		$customerIds	= array();
		foreach( $orders as $nr => $order ){
			$customerIds[]	= $order->customerId;
			$orders[$nr]->positions	= $this->logicShop->getOrderPositions( $order->orderId );
			$orders[$nr]->customer	= $this->logicShop->getCustomer( $order->customerId );
		}
//		$customers		= $this->modelCustomer->getAll( array( 'customerId' => $customerIds ) );
		$this->addData( 'orders', $orders );
		$this->addData( 'total', $this->logicShop->countOrders( $conditions ) );
		$this->addData( 'pageNr', $pageNr );
		$this->addData( 'filters', $this->session->getAll( 'module.manage_shop_order.filter.' ) );
//		$this->addData( 'customers', $customers );
	}

	public function setPositionStatus( $positionId, $status ){
		$this->logicShop->setOrderPositionStatus( $positionId, $status );
		$position	= $this->logicShop->getOrderPosition( $positionId );
		$this->restart( 'edit/'.$position->orderId, TRUE );
	}

	public function setStatus( $orderId, $status ){
		$this->logicShop->setOrderStatus( $orderId, $status );
		$this->restart( 'edit/'.$orderId, TRUE );
	}
}
?>
