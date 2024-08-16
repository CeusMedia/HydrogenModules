<?php
class Job_User_Provision extends Job_Abstract
{
	/**
	 *	@param		array		$parameters
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@todo rework
	 */
	public function manageLicenses( array $parameters = [] ): void
	{
//		$this->handleOutdatedUserLicenses();
		$this->manageOrderedLicenses();
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function manageOrderedLicenses(): void
	{
		$logicBridge	= new Logic_ShopBridge( $this->env );
		/** @var Logic_Shop $logicShop */
		$logicShop		= Logic_Shop::getInstance( $this->env );
		/** @var Logic_User_Provision $logicProvision */
		$logicProvision	= Logic_User_Provision::getInstance( $this->env );
		$bridgeId		= $logicBridge->getBridgeId( 'Provision' );

		$this->out( 'Provision Bridge ID: '.$bridgeId );
		$conditions	= ['status' => Model_Shop_Order::STATUS_PAYED];
		$orders		= ['orderId' => 'ASC'];
		foreach( $logicShop->getOrders( $conditions, $orders ) as $order ){
			$positions		= $logicShop->getOrderPositions( $order->orderId );
			$nrAllPositions	= count( $positions );
			$maxOrderStatus	= Model_Shop_Order::STATUS_DELIVERED;
			foreach( $positions as $nr => $position ){
				if( $position->bridgeId != $bridgeId ){
					if( $position->status != Model_Shop_Order_Position::STATUS_DELIVERED )
						$maxOrderStatus	= Model_Shop_Order::STATUS_PARTLY_DELIVERED;
					unset( $positions[$nr] );
				}
			}
			foreach( $positions as $nr => $position ){
				if( $position->status == Model_Shop_Order_Position::STATUS_DELIVERED )
					unset( $positions[$nr] );
			}
			if( !$positions )
				continue;
			$this->out( '- Order ID: '.$order->orderId );
			foreach( $positions as $nr => $position ){
				$license	= $logicProvision->getProductLicense( $position->articleId );
				$product	= $logicProvision->getProduct( $license->productId );
				$user		= $logicProvision->getUser( $order->userId );
				$this->out( '  - Position ID: '.$position->positionId );
				$this->out( '    - Product: ['.$product->productId.'] '.$product->title );
				$this->out( '    - License: ['.$license->productLicenseId.'] '.$license->title );
				$this->out( '    - User: ['.$user->userId.'] '.$user->username.' ('.$user->firstname.' '.$user->surname.')' );
//print_m( $this->env );die;
				$userLicenseId	= $logicProvision->addUserLicense( $order->userId, $license->productLicenseId, TRUE );
				$logicProvision->enableNextUserLicenseKeyForProduct( $order->userId, $license->productId );
				$logicProvision->activateUserLicense( $userLicenseId );
				$logicShop->setOrderPositionStatus( $position->positionId, Model_Shop_Order_Position::STATUS_DELIVERED );
			}
//			$this->out( '  - Order Status: '.$maxOrderStatus );
//			$logicShop->setOrderStatus( $order->orderId, $maxOrderStatus );
			break;
		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function handleOutdatedUserLicenses(): void
	{
		/** @var Logic_User_Provision $logic */
		$logic	= Logic_User_Provision::getInstance( $this->env );
		$keys	= $logic->handleExpiredLicenses();
		if( $keys ){
			$followUps	= 0;
			foreach( $keys as $key )
				$followUps	+= (int) isset( $key->nextKey );
			$this->out( 'Provision.expire: Disabled '.count( $keys ).' license(s).' );
			if( $followUps )
				$this->out( 'Provision: Enabled '.$followUps.' license(s) afterwards.' );
		}
	}
}
