<?php
class Controller_Manage_Shop_Order extends Controller_Manage_Shop{

	/**	@var		Logic_Shop			$logicShop */
	protected $logicShop;
	/**	@var		Logic_Catalog		$logicCatalog */
	protected $logicCatalog;

	protected function __onInit(){
		parent::__onInit();
		$this->logicShop	= new Logic_Shop( $this->env );
		$this->logicCatalog	= new Logic_Catalog( $this->env );

		$sessionPrefix		= 'module.manage_shop_order.filter.';
		if( !$this->session->get( $sessionPrefix.'order' ) )
				$this->session->set( $sessionPrefix.'order', 'created:DESC' );
		if( !$this->session->get( $sessionPrefix.'status' ) )
				$this->session->set( $sessionPrefix.'status', array( -5, 2, 3, 4, 5 ) );
	}

	public function edit( $orderId ){
		$order	= $this->logicShop->getOrder( $orderId, TRUE );
		foreach( $order->positions as $nr => $position )
			$order->positions[$nr]->article	= $this->logicCatalog->getArticle( $position->article_id );
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
						$model		= new Model_Customer( $this->env );
						$value		= '%'.str_replace( " ", "%", str_replace( ' ', '', $filterValue ) ).'%';
						$find		= array( 'CONCAT(firstname, lastname)' => $value );
						$customers	= $model->getAll( $find );
						$conditions['customer_id']	= array();
						foreach( $customers as $customer ){
							$conditions['customer_id'][]	= $customer->customer_id;
						}
						if( !$conditions['customer_id'] )
							unset( $conditions['customer_id'] );
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
			$customerIds[]	= $order->customer_id;
			$orders[$nr]->positions	= $this->logicShop->getOrderPositions( $order->order_id );
			$orders[$nr]->customer	= $this->logicShop->getCustomer( $order->customer_id );
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
		$this->restart( 'edit/'.$position->order_id, TRUE );
	}

	public function setStatus( $orderId, $status ){
		$this->logicShop->setOrderStatus( $orderId, $status );
		$this->restart( 'edit/'.$orderId, TRUE );
	}
}
?>
