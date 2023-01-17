<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_My_Order extends Controller
{
	protected array $backends	= [];
	protected $logicShop;
	protected $logicAuth;

	public function index( $page = 0 ){
		$limit		= 10;
		$conditions	= array( 'userId' => $this->logicAuth->getCurrentUserId() );
		$orders		= ['orderId' => 'DESC'];
		$limits		= [$page * $limit, $limit];
		$total		= count( $this->logicShop->getOrders( $conditions ) );
		$orders		= $this->logicShop->getOrders( $conditions, $orders, $limits );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $total / $limit ) );
		$this->addData( 'orders', $orders );
	}

	/**
	 *	Register a payment backend.
	 *	@access		public
	 *	@param		string		$backend		...
	 *	@param		string		$key			...
	 *	@param		string		$title			...
	 *	@param		string		$path			...
	 *	@param		integer		$priority		...
	 *	@param		string		$icon			...
	 *	@return		void
	 */
	public function registerPaymentBackend( $backend, $key, $title, $path, $priority = 5, $icon = NULL, $countries = [] ){
		$this->backends[]	= (object) [
			'backend'	=> $backend,
			'key'		=> $key,
			'title'		=> $title,
			'path'		=> $path,
			'priority'	=> $priority,
			'icon'		=> $icon,
			'countries'	=> $countries,
		];
	}

	public function view( $orderId ){
		$order	= $this->logicShop->getOrder( $orderId, TRUE );
		if( $order->userId !== $this->logicAuth->getCurrentUserId() ){
			$this->env->getMessenger()->noteError( 'Zugriff verweigert.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'order', $order );
	}

	protected function __onInit(): void
	{
		$this->logicShop	= $this->env->getLogic()->shop;
		$this->logicAuth	= $this->env->getLogic()->authentication;

		$payload	= [];
		$captain	= $this->env->getCaptain();
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, $payload );
		$backends	= [];
		foreach( $this->backends as $backend )
			$backends[$backend->key]	= $backend;
		$this->addData( 'paymentBackends', $backends );
	}
}
