<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Shop_Payment_Bank extends Controller
{
	/**	@var	Dictionary					$config			Module configuration dictionary */
	protected $config;

	/**	@var	Logic_Shop					$logicShop		Shop logic instance */
	protected $logicShop;

	/**	@var	Net_HTTP_PartitionSession	$session		Session resource */
	protected $session;

	protected $request;
	protected $messenger;
	protected $orderId;
	protected $order;
	protected $localUserId;
	protected $userId;
	protected $wallet;
	protected $backends			= [];

	/**
	 *	Entry point for payment.
	 *	Since "Transfer" (user pays on another channel) is selected, no further actions are needed.
	 *	Forwards to shop finish.
	 */
	public function perTransfer()
	{
		$this->restart( 'shop/finish' );
	}

	/**
	 *	Entry point for payment.
	 *	Since "Bill" (user pays bill coming on delivery) is selected, no further actions are needed.
	 *	Forwards to shop finish.
	 */
	public function perBill()
	{
		$this->restart( 'shop/finish' );
	}

	public function registerPaymentBackend( $backend, string $key, string $title, string $path, int $priority = 5, string $icon = NULL )
	{
		$this->backends[]	= (object) array(
			'backend'	=> $backend,
			'key'		=> $key,
			'title'		=> $title,
			'path'		=> $path,
			'priority'	=> $priority,
			'icon'		=> $icon,
			'mode'		=> 'delayed',
		);
	}

	protected function __onInit(): void
	{
		$this->session			= $this->env->getSession();
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
//		$this->configPayment	= $this->env->getConfig()->getAll( 'module.shop_payment.bank.', TRUE );
		$this->configShop		= $this->env->getConfig()->getAll( 'module.shop.', TRUE );
		$this->logicShop		= new Logic_Shop( $this->env );

		$captain	= $this->env->getCaptain();
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, [] );
		$this->addData( 'paymentBackends', $this->backends );
		$this->addData( 'configShop', $this->configShop );

		$modelCart			= new Model_Shop_Cart( $this->env );
		$this->orderId		= $modelCart->get( 'orderId' );
		if( !$this->orderId ){
			$this->messenger->noteError( 'Invalid order' );
			$this->restart( 'shop' );
		}
		$this->order		= $this->logicShop->getOrder( $this->orderId );
	}
}
