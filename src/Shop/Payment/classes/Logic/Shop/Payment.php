<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_Shop_Payment extends Logic
{
	/**	@var		Model_Shop_Payment_BackendRegister			$modelBackend */
	protected Model_Shop_Payment_BackendRegister $backends;

	/**
	 * Use hook to call for payment modules to register payment backends/methods.
	 * @return self
	 * @throws ReflectionException
	 */
	public function collectBackends(): self
	{
		$captain	= $this->env->getCaptain();
		$payload	= ['register' => new Model_Shop_Payment_BackendRegister( $this->env )];
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, $payload );
		$this->backends	= $payload['register'];
		return $this;
	}

	/**
	 *	...
	 *	@param		float			$totalPrice
	 *	@param		object|string	$backend
	 *	@param		string			$countryCode
	 *	@return		float|NULL
	 */
	public function getPrice( float $totalPrice, object|string $backend, string $countryCode ): ?float
	{
		if( is_string( $backend ) ){
			$backends	= $this->getBackends();
			if( !$backends->has( $backend ) )
				throw new DomainException( 'Invalid payment backend: '.$backend );
			$backend	= $backends->get( $backend );
		}

		if( $backend->countries && !in_array( $countryCode, $backend->countries, TRUE ) )
			return NULL;
		if( '' === $backend->feeFormula || '0' === $backend->feeFormula )
			return NULL;

		$price  = $backend->feeFormula;
		if( str_contains( $price, '%' ) ){
			$formula    = str_replace( '%', '*'.( $totalPrice / 100), $price );
			$price      = eval( 'return ('.$formula.');' );
		}
		return $price;
	}

	public function getBackends( bool $autoload = TRUE ): ?Model_Shop_Payment_BackendRegister
	{
		if( 0 === $this->backends->count() && $autoload )
			$this->collectBackends();
		return $this->backends;
	}

	public function setBackends( Model_Shop_Payment_BackendRegister $backends ): self
	{
		$this->backends	= $backends;
		return $this;
	}

	protected function __onInit(): void
	{
		$this->backends	= new Model_Shop_Payment_BackendRegister( $this->env );
	}
}
