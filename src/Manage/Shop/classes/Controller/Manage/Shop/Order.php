<?php

class Controller_Manage_Shop_Order extends Controller_Manage_Shop
{
	public function edit( $orderId ): void
	{
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

	public function filter( $reset = FALSE ): void
	{
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

	public function index( $pageNr = 0 ): void
	{
		$filters	= $this->session->getAll( 'module.manage_shop_order.filter.' );
		$orders		= [];
		$conditions	= [];
		foreach( $filters as $filterKey => $filterValue ){
			switch( $filterKey ){
				case 'customer':
					if( strlen( trim( $filterValue ) ) ){
						$model		= new Model_Shop_Customer( $this->env );
						$value		= '%'.str_replace( " ", "%", str_replace( ' ', '', $filterValue ) ).'%';
						$find		= array( 'CONCAT(firstname, surname)' => $value );
						/** @var array<Entity_Shop_Customer> $customers */
						$customers	= $model->getAll( $find );
						if( [] !== $customers ){
							$conditions['userId']	= [];
							foreach( $customers as $customer )
								$conditions['userId'][]	= $customer->userId;
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

		$orders			= $this->logicShop->getOrders( $conditions, $orders, [$pageNr * 15, 15] );
		$customerIds	= [];
		foreach( $orders as $nr => $order ){
			$customerIds[]	= $order->userId;
			$orders[$nr]->positions	= $this->logicShop->getOrderPositions( $order->orderId );
			$orders[$nr]->customer	= $this->logicShop->getOrderCustomer( $order->orderId );
		}
//		$customers		= $this->modelCustomer->getAll( ['userId' => $customerIds] );
		$this->addData( 'orders', $orders );
		$this->addData( 'total', $this->logicShop->countOrders( $conditions ) );
		$this->addData( 'pageNr', $pageNr );
		$this->addData( 'filters', $this->session->getAll( 'module.manage_shop_order.filter.' ) );
//		$this->addData( 'customers', $customers );
	}

	public function setPositionStatus( $positionId, $status ): void
	{
		$this->logicShop->setOrderPositionStatus( $positionId, $status );
		$position	= $this->logicShop->getOrderPosition( $positionId );
		$this->restart( 'edit/'.$position->orderId, TRUE );
	}

	public function setStatus( $orderId, $status ): void
	{
		$this->logicShop->setOrderStatus( $orderId, $status );
		$this->restart( 'edit/'.$orderId, TRUE );
	}

	protected function __onInit(): void
	{
		parent::__onInit();
		$this->logicShop	= new Logic_ShopManager( $this->env );
		$this->logicBridge	= new Logic_ShopBridge( $this->env );

		$sessionPrefix		= 'module.manage_shop_order.filter.';
		if( !$this->session->get( $sessionPrefix.'order' ) )
				$this->session->set( $sessionPrefix.'order', 'createdAt:DESC' );
		if( !$this->session->get( $sessionPrefix.'status' ) )
				$this->session->set( $sessionPrefix.'status', [-5, 2, 3, 4, 5] );
	}
}
