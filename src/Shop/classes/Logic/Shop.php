<?php

class Logic_Shop extends Logic_ShopResource
{
	/**	@var	Model_Shop_Cart				$modelCart */
	protected Model_Shop_Cart $modelCart;

//	/** @var	Dictionary					$moduleConfig */
//	protected Dictionary $moduleConfig;

	public function countArticleInCart( int|string $bridgeId, int|string $articleId ): int
	{
		if( is_array( ( $positions = $this->modelCart->get( 'positions' ) ) ) )
			foreach( $positions as $position )
				if( $position->bridgeId == $bridgeId && $position->articleId == $articleId )
					return (int) $position->quantity;
		return 0;
	}

	public function countArticlesInCart( bool $countEach = FALSE ): int
	{
		$number	= 0;
		if( is_array( ( $positions = $this->modelCart->get( 'positions' ) ) ) ){
			if( !$countEach )
				return count( $positions );
			foreach( $positions as $position )
				$number	+= $position->quantity;
		}
		return $number;
	}

	public function getBillingAddressFromCart(): ?Entity_Address
	{
		$address		= NULL;
		if( $this->modelCart->get( 'userId' ) ){
			/** @var ?Entity_Address $address */
			$address	= $this->modelAddress->getByIndices( [
				'relationId'	=> $this->modelCart->get( 'userId' ),
				'relationType'	=> 'user',
				'type'			=> Model_Address::TYPE_BILLING,
			] );
		}
		return $address;
	}

	public function getDeliveryAddressFromCart(): ?Entity_Address
	{
		$address		= NULL;
		if( $this->modelCart->get( 'userId' ) ){
			/** @var ?Entity_Address $address */
			$address	= $this->modelAddress->getByIndices( [
				'relationId'	=> $this->modelCart->get( 'userId' ),
				'relationType'	=> 'user',
				'type'			=> Model_Address::TYPE_DELIVERY,
			] );
		}
		return $address;
	}

	public function getPaymentFeesFromCart(): ?float
	{
		if( !$this->usePayment )
			return NULL;

		$backend	= $this->modelCart->get( 'paymentMethod' );
		$address	= $this->getDeliveryAddressFromCart();
		return $this->logicPayment->getPrice( $this->modelCart->getTotal(), $backend, $address->country );
	}

	public function hasArticleInCart( int|string $bridgeId, int|string $articleId ): bool
	{
		return $this->countArticleInCart( $bridgeId, $articleId ) > 0;
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		parent::__onInit();
		$this->modelCart	= new Model_Shop_Cart( $this->env );
	}
}
