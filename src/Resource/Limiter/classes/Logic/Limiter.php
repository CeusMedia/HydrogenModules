<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	@todo		apply module config main switch
 */
class Logic_Limiter
{
	public const OPERATION_BOOLEAN				= 0;
	public const OPERATION_COMPARE_NUMBER		= 1;

	protected static ?self $instance			= NULL;

	protected Environment $env;

	protected Dictionary $rules;

	protected bool $enabled;

	protected Dictionary $moduleConfig;

	public static function getInstance( $env ): self
	{
		if( NULL === self::$instance )
			self::$instance		= new self( $env );
		return self::$instance;
	}

	public function allows( string $key, $value = NULL, int $operation = self::OPERATION_COMPARE_NUMBER )
	{
		if( !$this->rules->has( $key ) )
			return TRUE;
		if( $value === NULL )
			$operation	= self::OPERATION_BOOLEAN;
		switch( $operation ){
			case self::OPERATION_BOOLEAN:
				return (bool) $this->rules->get( $key );
			case self::OPERATION_COMPARE_NUMBER:
				return $this->rules->get( $key ) >= $value;
		}
	}

	public function denies( string $key, $value = NULL, int $operation = self::OPERATION_COMPARE_NUMBER ): bool
	{
		if( !$this->rules->has( $key ) )
			return FALSE;
		return !$this->allows( $key, $value, $operation );
	}


	public function get( string $key )
	{
		return $this->rules->get( $key );
	}

	/**
	 *	Returns all rules of limiter as an array.
	 *	Using a filter prefix, all rules with keys starting with prefix are returned.
	 *	Attention: A given prefix will be cut from rule keys.
	 *	By default, an array is returned. Alternatively another dictionary can be returned.
	 *	@access		public
	 *	@param		string|NULL		$prefix			Prefix to filter keys, e.g. "mail." for all rules starting with "mail."
	 *	@param		boolean			$asDictionary	Flag: return list as dictionary object instead of an array
	 *	@param		boolean			$caseSensitive	Flag: return list with lowercase rule keys or dictionary with no case sensitivity
	 *	@return		array|Dictionary				Map or dictionary object containing all or filtered rules
	 */
	public function getAll( ?string $prefix = NULL, bool $asDictionary = FALSE, bool $caseSensitive = TRUE ): array|Dictionary
	{
		return $this->rules->getAll( $prefix, $asDictionary, $caseSensitive );
	}

	public function getRules(): Dictionary
	{
		return $this->rules;
	}

	public function has( $key ): bool
	{
		return $this->rules->has( $key );
	}

	public function index(): array
	{
		return $this->rules->getAll();
	}

	public function set( $key, $value ): ?bool
	{
		if( !$this->enabled )
			return NULL;
		return $this->rules->set( $key, $value );
	}

	protected function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->moduleConfig	= $env->getConfig()->getAll( 'module.resource_limiter.', TRUE );
		$this->enabled		= $this->moduleConfig->get( 'active' );
		$this->rules		= new Dictionary();
		$this->__onInit();
	}

	protected function __clone()
	{
	}

	protected function __onInit(): void
	{
//		$this->env->getCaptain()->callHook( 'Limiter', 'registerLimits', $this );
	}
}
